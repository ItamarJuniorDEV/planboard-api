<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectStatsService
{
    /** @return array<string, mixed> */
    public function get(Project $project): array
    {
        /** @var array<string, mixed> $stats */
        $stats = Cache::remember($this->key($project->id), 60, function () use ($project): array {
            $tasksByStatus = $project->tasks()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get();

            $tasksByPriority = $project->tasks()
                ->selectRaw('priority, count(*) as total')
                ->groupBy('priority')
                ->get();

            $subtasks = $project->tasks()
                ->withCount([
                    'subtasks',
                    'subtasks as subtasks_done_count' => fn ($query) => $query->where('done', true),
                ])
                ->get();

            $totalSubtasks = $subtasks->sum('subtasks_count');
            $doneSubtasks = $subtasks->sum('subtasks_done_count');

            $totalMilestones = $project->milestones()->count();
            $overdueMilestones = $project->milestones()
                ->whereDate('due_date', '<', now())
                ->count();

            return [
                'tasks' => [
                    'by_status' => $tasksByStatus,
                    'by_priority' => $tasksByPriority,
                ],
                'subtasks' => [
                    'total' => $totalSubtasks,
                    'done' => $doneSubtasks,
                    'pending' => $totalSubtasks - $doneSubtasks,
                ],
                'milestones' => [
                    'total' => $totalMilestones,
                    'overdue' => $overdueMilestones,
                ],
            ];
        });

        return $stats;
    }

    public function invalidate(int $projectId): void
    {
        Cache::forget($this->key($projectId));
    }

    private function key(int $projectId): string
    {
        return "project:{$projectId}:stats";
    }
}
