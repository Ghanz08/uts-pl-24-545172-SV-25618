<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ProjectIdea
 */
class ProjectIdeaResource extends JsonResource
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
            'title' => $this->title,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'tech_stack' => $this->tech_stack ?? [],
            'difficulty' => $this->difficulty,
            'status' => $this->status,
            'is_completed' => $this->isCompleted(),
            'origin' => $this->origin,
            'hype_level' => $this->hype_level,
            'target_event' => $this->target_event,
            'deadline' => $this->deadline?->toDateString(),
            'repo_url' => $this->repo_url,
            'is_pinned' => $this->is_pinned,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
