<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{
    return [
        'id'           => $this->id,
        'slug'         => $this->slug,
        'title'        => $this->title,
        'severity'     => $this->severity,
        'status'       => $this->status,
        'description'  => $this->description,
        
        // 1. Include the AI's Analysis
        'ai_summary'   => $this->ai_summary,

        // 2. THE ELITE MOVE: Similarity Score
        // This only shows up when we are doing an AI Search.
        // In Postgres, similarity is (1 - distance). 
        'similarity'   => $this->when(isset($this->distance), function() {
            return round((1 - $this->distance) * 100, 2) . '%';
        }),

        'evidence_image_url' => $this->evidence_image ? asset('storage/' . $this->evidence_image) : null,
        'created_at'   => $this->created_at->diffForHumans(), // Human readable for the UI
        'submitted_by' => new UserResource($this->whenLoaded('user')),
        'program'      => new ProgramResource($this->whenLoaded('program')),
    ];
}
}
