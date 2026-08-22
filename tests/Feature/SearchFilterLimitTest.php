<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtros_de_busca_limitam_o_tamanho_do_termo(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $search = str_repeat('a', 101);

        $this->actingAs($user, 'sanctum');

        $urls = [
            '/api/projects?search='.$search,
            "/api/projects/{$project->id}/boards?search={$search}",
            "/api/projects/{$project->id}/tasks?search={$search}",
            "/api/projects/{$project->id}/milestones?search={$search}",
            "/api/projects/{$project->id}/labels?search={$search}",
        ];

        foreach ($urls as $url) {
            $this->getJson($url)
                ->assertStatus(422)
                ->assertJsonValidationErrors(['search']);
        }
    }
}
