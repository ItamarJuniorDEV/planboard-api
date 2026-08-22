<?php

namespace App\Observers;

use App\Models\Milestone;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class InvalidatesProjectStats
{
    public function saved(Task|Subtask|Milestone $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Task|Subtask|Milestone $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Task|Subtask|Milestone $model): void
    {
        $projectId = $model instanceof Subtask
            ? $model->task()->first()?->project_id
            : $model->project_id;

        if ($projectId === null) {
            return;
        }

        Cache::forget("project:{$projectId}:stats");
    }
}
