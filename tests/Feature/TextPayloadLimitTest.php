<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TextPayloadLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_description_is_limited_on_create_and_update(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Projeto',
            'description' => str_repeat('a', 5001),
            'budget' => 100,
            'status' => 'active',
        ];

        $this->postJson('/api/projects', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('description');

        $project = Project::factory()->for($user)->create();

        $this->putJson("/api/projects/{$project->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    }

    public function test_task_description_is_limited_on_create_and_update(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->for($user)->for($project)->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Tarefa',
            'description' => str_repeat('a', 5001),
            'priority' => 'medium',
            'status' => 'todo',
        ];

        $this->postJson("/api/projects/{$project->id}/tasks", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('description');

        $this->putJson("/api/projects/{$project->id}/tasks/{$task->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('description');
    }

    public function test_comment_content_is_limited_on_create_and_update(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $task = Task::factory()->for($user)->for($project)->create();
        $comment = Comment::factory()->for($user)->for($task)->create();
        Sanctum::actingAs($user);

        $payload = ['content' => str_repeat('a', 2001)];

        $this->postJson("/api/projects/{$project->id}/tasks/{$task->id}/comments", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('content');

        $this->putJson("/api/projects/{$project->id}/tasks/{$task->id}/comments/{$comment->id}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('content');
    }
}
