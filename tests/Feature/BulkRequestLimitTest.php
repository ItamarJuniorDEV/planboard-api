<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkRequestLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Project $project;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user)->create();
        $this->task = Task::factory()->for($this->project)->for($this->user)->create();
    }

    public function test_bulk_delete_de_tarefas_limita_o_tamanho_do_lote(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/tasks/bulk-delete", [
                'task_ids' => range(1, 101),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['task_ids']);
    }

    public function test_bulk_move_de_tarefas_limita_o_tamanho_do_lote(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/projects/{$this->project->id}/tasks/bulk-move", [
                'task_ids' => range(1, 101),
                'column_id' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['task_ids']);
    }

    public function test_operacoes_em_lote_de_subtarefas_limitam_o_tamanho_do_lote(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/bulk-complete", [
                'subtask_ids' => range(1, 101),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subtask_ids']);
    }

    public function test_bulk_delete_de_comentarios_limita_o_tamanho_do_lote(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments/bulk-delete", [
                'comment_ids' => range(1, 101),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['comment_ids']);
    }
}
