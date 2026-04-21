<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelationshipManagerResource extends JsonResource
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
            'name' => $this->username ?? null,
            'email' => $this->email ?? null,
            'phone' => $this->phone ?? null,
            'role' => $this->role?->name ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
