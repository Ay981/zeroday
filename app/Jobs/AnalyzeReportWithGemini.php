<?php

namespace App\Jobs;

use App\Models\Report;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyzeReportWithGemini implements ShouldQueue
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function handle(): void
    {
        Log::info("AI: Commencing analysis for Report #{$this->report->id}");

        $apiKey = env('GEMINI_API_KEY');
// Update this specific line:
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";        // The "System Prompt" - Instructing the AI on how to act
        $prompt = "You are a Senior Cybersecurity Analyst. Read this bug report title and description. Provide a strict, 2-sentence executive summary of the risk and business impact. Do not use markdown, just plain text.\n\nTitle: {$this->report->title}\nDescription: {$this->report->description}";

        // The HTTP Call to Google
        $response = Http::post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

        if ($response->successful()) {
            // Extract the text from Google's complex JSON structure
            $aiText = $response->json('candidates.0.content.parts.0.text');

            // Save it to the database
            $this->report->update(['ai_summary' => trim($aiText)]);
            
            Log::info("AI: Analysis complete for Report #{$this->report->id}");
        } else {
            Log::error("AI: Gemini API Failed", ['error' => $response->body()]);
            // Throwing an exception tells Laravel to put the job back in the Queue and try again later
            throw new \Exception("Gemini API Request Failed");
        }
    }
}