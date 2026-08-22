<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Column;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lista_apenas_projetos_do_usuario_autenticado(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Project::factory()->for($owner)->create(['title' => 'Projeto do owner']);
        Project::factory()->for($otherUser)->create(['title' => 'Projeto privado de outro usuário']);

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/projects');

        $response->assertOk();

        $titles = collect($response->json('data.data'))->pluck('title');

        $this->assertTrue($titles->contains('Projeto do owner'));
        $this->assertFalse($titles->contains('Projeto privado de outro usuário'));
    }

    public function test_usuario_nao_lista_quadros_de_projeto_de_outro_usuario(): void
    {
        [$user, $foreignOwner, $foreignProject] = $this->foreignProject();

        Board::factory()->for($foreignProject)->for($foreignOwner)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/projects/{$foreignProject->id}/boards")
            ->assertForbidden();
    }

    public function test_usuario_nao_lista_tarefas_de_projeto_de_outro_usuario(): void
    {
        [$user, $foreignOwner, $foreignProject] = $this->foreignProject();

        Task::factory()->for($foreignProject)->for($foreignOwner)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/projects/{$foreignProject->id}/tasks")
            ->assertForbidden();
    }

    public function test_usuario_nao_lista_marcos_de_projeto_de_outro_usuario(): void
    {
        [$user, $foreignOwner, $foreignProject] = $this->foreignProject();

        Milestone::factory()->for($foreignProject)->for($foreignOwner)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/projects/{$foreignProject->id}/milestones")
            ->assertForbidden();
    }

    public function test_usuario_nao_lista_recursos_aninhados_de_outro_usuario(): void
    {
        [$user, $foreignOwner, $foreignProject] = $this->foreignProject();
        $foreignBoard = Board::factory()->for($foreignProject)->for($foreignOwner)->create();
        $foreignTask = Task::factory()->for($foreignProject)->for($foreignOwner)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/projects/{$foreignProject->id}/boards/{$foreignBoard->id}/columns")
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/projects/{$foreignProject->id}/tasks/{$foreignTask->id}/subtasks")
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/projects/{$foreignProject->id}/tasks/{$foreignTask->id}/comments")
            ->assertForbidden();
    }

    public function test_usuario_nao_cria_recursos_em_projeto_de_outro_usuario(): void
    {
        [$user, $foreignOwner, $foreignProject] = $this->foreignProject();
        $foreignBoard = Board::factory()->for($foreignProject)->for($foreignOwner)->create();
        $foreignColumn = Column::factory()->for($foreignBoard)->for($foreignOwner)->create();
        $foreignTask = Task::factory()
            ->for($foreignProject)
            ->for($foreignColumn)
            ->for($foreignOwner)
            ->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/boards", [
                'name' => 'Quadro indevido',
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/boards/{$foreignBoard->id}/columns", [
                'name' => 'Coluna indevida',
                'position' => 1,
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/tasks", [
                'title' => 'Tarefa indevida',
                'priority' => 'medium',
                'status' => 'todo',
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/tasks/{$foreignTask->id}/subtasks", [
                'title' => 'Subtarefa indevida',
                'done' => false,
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/tasks/{$foreignTask->id}/comments", [
                'content' => 'Comentário indevido',
                'author' => 'Outro usuário',
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/milestones", [
                'title' => 'Marco indevido',
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/labels", [
                'name' => 'Label indevida',
                'color' => '#000000',
            ])
            ->assertForbidden();
    }

    public function test_usuario_nao_executa_operacoes_em_lote_em_recursos_de_outro_usuario(): void
    {
        [$user, $foreignOwner, $foreignProject] = $this->foreignProject();
        $foreignBoard = Board::factory()->for($foreignProject)->for($foreignOwner)->create();
        $foreignColumn = Column::factory()->for($foreignBoard)->for($foreignOwner)->create();
        $foreignTask = Task::factory()
            ->for($foreignProject)
            ->for($foreignColumn)
            ->for($foreignOwner)
            ->create();
        $foreignSubtask = Subtask::factory()->for($foreignTask)->for($foreignOwner)->create(['done' => false]);
        $foreignComment = Comment::factory()->for($foreignTask)->for($foreignOwner)->create();

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/projects/{$foreignProject->id}/tasks/bulk-move", [
                'task_ids' => [$foreignTask->id],
                'column_id' => $foreignColumn->id,
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/tasks/bulk-delete", [
                'task_ids' => [$foreignTask->id],
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/tasks/{$foreignTask->id}/subtasks/bulk-complete", [
                'subtask_ids' => [$foreignSubtask->id],
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/tasks/{$foreignTask->id}/subtasks/bulk-delete", [
                'subtask_ids' => [$foreignSubtask->id],
            ])
            ->assertForbidden();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$foreignProject->id}/tasks/{$foreignTask->id}/comments/bulk-delete", [
                'comment_ids' => [$foreignComment->id],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $foreignTask->id]);
        $this->assertDatabaseHas('subtasks', ['id' => $foreignSubtask->id, 'done' => false]);
        $this->assertDatabaseHas('comments', ['id' => $foreignComment->id]);
    }

    /** @return array{User, User, Project} */
    private function foreignProject(): array
    {
        $user = User::factory()->create();
        $foreignOwner = User::factory()->create();
        $foreignProject = Project::factory()->for($foreignOwner)->create();

        return [$user, $foreignOwner, $foreignProject];
    }
}
