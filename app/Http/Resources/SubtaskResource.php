<?php

namespace App\Http\Resources;

use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Subtask */
class SubtaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'title' => $this->title,
            'done' => (bool) $this->done,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
