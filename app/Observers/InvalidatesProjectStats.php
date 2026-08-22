<?php

namespace App\Observers;

use App\Models\Milestone;
use App\Models\Subtask;
use App\Models\Task;
use App\Services\ProjectStatsService;

class InvalidatesProjectStats
{
    public function __construct(private readonly ProjectStatsService $stats) {}

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
            ? $model->task()->value('project_id')
            : $model->project_id;

        if ($projectId === null) {
            return;
        }

        $this->stats->invalidate((int) $projectId);
    }
}
