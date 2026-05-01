<?php

namespace App\Jobs;

use App\Models\Report;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AnalyzeReportWithGemini implements ShouldQueue
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function handle(): void
    {
        Log::info("AI: Commencing analysis for Report #{$this->report->id}");

        $apiKey = config('services.gemini.key');

        if ($apiKey === null || $apiKey === '') {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
        $prompt = "You are a Senior Cybersecurity Analyst. Read this bug report title and description. Provide a strict, 2-sentence executive summary of the risk and business impact. Do not use markdown, just plain text.\n\nTitle: {$this->report->title}\nDescription: {$this->report->description}";

        $response = Http::timeout(30)
            ->retry(3, 250)
            ->acceptJson()
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

        if ($response->successful()) {
            $aiText = $response->json('candidates.0.content.parts.0.text');
            $this->report->update(['ai_summary' => trim((string) $aiText)]);

            $this->storeSemanticEmbedding($apiKey);

            Log::info("AI: Analysis complete for Report #{$this->report->id}");
        } else {
            Log::error('AI: Gemini API Failed', ['error' => $response->body()]);
            throw new RuntimeException('Gemini API Request Failed');
        }
    }

    private function storeSemanticEmbedding(string $apiKey): void
{
    $embedUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-2:embedContent?key={$apiKey}";

    $text = "Title: {$this->report->title}. Description: {$this->report->description}";

    $embedResponse = Http::timeout(30)
        ->retry(3, 250)
        ->acceptJson()
        ->post($embedUrl, [
            'content' => [
                'parts' => [
                    [
                        'text' => $text
                    ]
                ]
            ],
            'output_dimensionality' => 768
        ]);

    if (! $embedResponse->successful()) {
        Log::warning("AI: Embedding failed for Report #{$this->report->id}", [
            'error' => $embedResponse->body(),
        ]);
        return;
    }

    $vector = $embedResponse->json('embedding.values') ?? [];

    if (!is_array($vector) || empty($vector)) {
        Log::warning("AI: Empty embedding for Report #{$this->report->id}");
        return;
    }

    // store as JSON (better than manual string)
    $this->report->update([
        'embedding' => $vector,
    ]);

    Log::info("AI: Embedding stored for Report #{$this->report->id}");
}
}
