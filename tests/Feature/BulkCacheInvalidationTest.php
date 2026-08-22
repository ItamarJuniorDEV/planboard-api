<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BulkCacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Project $project;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user)->create();
        $this->task = Task::factory()->for($this->project)->for($this->user)->create();
    }

    public function test_bulk_delete_de_tarefas_invalida_stats(): void
    {
        $task = Task::factory()->for($this->project)->for($this->user)->create();
        $this->cacheStats();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/tasks/bulk-delete", [
                'task_ids' => [$task->id],
            ])
            ->assertOk();

        $this->assertFalse(Cache::has($this->cacheKey()));
    }

    public function test_bulk_complete_de_subtarefas_invalida_stats(): void
    {
        $subtask = Subtask::factory()->for($this->task)->for($this->user)->create(['done' => false]);
        $this->cacheStats();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/bulk-complete", [
                'subtask_ids' => [$subtask->id],
            ])
            ->assertOk();

        $this->assertFalse(Cache::has($this->cacheKey()));
    }

    public function test_bulk_delete_de_subtarefas_invalida_stats(): void
    {
        $subtask = Subtask::factory()->for($this->task)->for($this->user)->create();
        $this->cacheStats();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/bulk-delete", [
                'subtask_ids' => [$subtask->id],
            ])
            ->assertOk();

        $this->assertFalse(Cache::has($this->cacheKey()));
    }

    private function cacheStats(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/stats")
            ->assertOk();

        $this->assertTrue(Cache::has($this->cacheKey()));
    }

    private function cacheKey(): string
    {
        return "project:{$this->project->id}:stats";
    }
}
