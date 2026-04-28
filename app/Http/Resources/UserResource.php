<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reputation' => $this->reputation,
            'level' => (int) floor(((int) $this->reputation) / 100),
            'role' => $this->role,
            'otp_verified' => $this->otp_verified ?? false,
            'is_verified' => $this->otp_verified ?? false,
        ];
    }
}
