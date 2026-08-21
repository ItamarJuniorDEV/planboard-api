<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Milestone;
use App\Models\Project;
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

    private function foreignProject(): array
    {
        $user = User::factory()->create();
        $foreignOwner = User::factory()->create();
        $foreignProject = Project::factory()->for($foreignOwner)->create();

        return [$user, $foreignOwner, $foreignProject];
    }
}
