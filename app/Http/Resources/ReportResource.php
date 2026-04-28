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
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'program_id' => $this->program_id,
            'program' => new ProgramResource($this->whenLoaded('program')),
            'ai_summary' => $this->ai_summary,
            'severity' => $this->severity,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'submitted_by' => new UserResource($this->whenLoaded('user')),
            'evidence_image_url' => $this->evidence_image ? asset('storage/' . $this->evidence_image) : null,

        ];
    }
}
