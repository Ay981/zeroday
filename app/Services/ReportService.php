<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Program;
use App\Models\User;
use App\Events\ReportSubmitted; // <--- The Background Queue Trigger
use App\Jobs\AnalyzeReportWithGemini;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ReportService
{
    /**
     * Read: Handle filtering, eager loading, and pagination (The GIN Search Engine)
     */
    public function listReports(array $filters, int $perPage = 15)
    {
        return Report::with(['user', 'program'])
            ->latest()
            ->filter($filters) // Hits your Model's scopeFilter
            ->paginate($perPage);
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