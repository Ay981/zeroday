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
            'slug' => $this->slug,
            'title' => $this->title,
            'severity' => $this->severity,
            'status' => $this->status,
            'description' => $this->description,
            'ai_summary' => $this->ai_summary,
            'similarity' => $this->when(isset($this->distance), function () {
                return round((1 - $this->distance) * 100, 2).'%';
            }),
            'evidence_image_url' => $this->evidence_image
                ? url('api/v1/evidence/'.basename($this->evidence_image))
                : null,
            'created_at' => $this->created_at->diffForHumans(),
            'submitted_by' => new UserResource($this->whenLoaded('user')),
            'program' => new ProgramResource($this->whenLoaded('program')),
        ];
    }
}
