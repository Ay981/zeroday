<?php

namespace App\Services;

use App\Jobs\AnalyzeReportWithGemini;
use App\Models\Program;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    /**
     * Read: Handle filtering, eager loading, and pagination (The GIN Search Engine)
     */
    public function listReports(array $filters)
    {
        $query = Report::with(['user', 'program']);

        if (! empty($filters['search']) && ($filters['ai_mode'] ?? 'false') === 'true') {
            $searchVector = $this->getGeminiEmbedding($filters['search']);

            if ($searchVector !== []) {
                return $query->searchSemantic($searchVector)->paginate(15);
            }

            Log::warning('AI semantic search skipped because Gemini did not return an embedding.');
        }

        return $query->latest()->filter($filters)->paginate(15);
    }

    private function getGeminiEmbedding(string $text): array
{
    $apiKey = config('services.gemini.key');

    if (!$apiKey) {
        Log::warning('Gemini API key missing');
        return [];
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-2:embedContent?key={$apiKey}";

    $response = Http::timeout(30)
        ->retry(3, 250)
        ->acceptJson()
        ->post($url, [
            'content' => [
                'parts' => [
                    [
                        'text' => "task: search result | query: {$text}"
                    ]
                ]
            ],
            'output_dimensionality' => 768
        ]);

    if (!$response->successful()) {
        Log::warning('Gemini embedding failed', [
            'error' => $response->body(),
        ]);

        return [];
    }

    return $response->json('embedding.values') ?? [];
}

    /**
     * Create: The Main Orchestrator
     */
    public function createReport(array $data): Report
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $program = Program::findOrFail($data['program_id']);

            // 1. Handle the Image
            $data = $this->handleFileUpload($data);

            // 2. Create the DB row
            $report = $this->storeReportRecord($user, $data);

            // 3. Award Points
            $this->awardReputation($user, $program, $data['severity']);

            // 4. AI Analysis
            AnalyzeReportWithGemini::dispatch($report);

            return $report;
        });
    }

    /**
     * Update: Hydrate and Modify
     */
    public function updateReport(Report $report, array $data): Report
    {
        $data = $this->handleFileUpload($data, $report->evidence_image);

        $report->update($data);

        return $report;
    }

    /*
    |--------------------------------------------------------------------------
    | Private Business Logic Modules
    |--------------------------------------------------------------------------
    */

    private function handleFileUpload(array $data, ?string $oldFilePath = null): array
    {
        // Handle image removal
        if (isset($data['remove_image']) && in_array($data['remove_image'], [true, 'true', 1, '1'])) {
            if ($oldFilePath) {
                Storage::disk('public')->delete($oldFilePath);
            }
            $data['evidence_image'] = null;
            unset($data['remove_image']);
        }
        // Handle new image upload
        elseif (isset($data['evidence_image']) && $data['evidence_image'] instanceof UploadedFile) {
            // Delete old file if updating
            if ($oldFilePath) {
                Storage::disk('public')->delete($oldFilePath);
            }
            // Replace the File object with the String path
            $data['evidence_image'] = $data['evidence_image']->store('evidence', 'public');
        }

        return $data;
    }

    private function storeReportRecord(User $user, array $data): Report
    {
        return $user->reports()->create($data);
    }

    private function awardReputation(User $user, Program $program, string $severity): void
    {
        $pointsMap = ['Low' => 10, 'Medium' => 25, 'High' => 50, 'Critical' => 100];
        $basePoints = $pointsMap[$severity] ?? 0;
        $finalPoints = (int) ($basePoints * $program->bounty_multiplier);

        $user->increment('reputation', $finalPoints);
    }
}
