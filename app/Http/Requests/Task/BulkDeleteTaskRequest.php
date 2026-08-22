<?php

namespace App\Http\Requests\Task;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $user = $this->user();

        return $project instanceof Project
            && $user !== null
            && $user->can('view', $project)
            && $user->can('viewAny', Task::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'task_ids' => ['required', 'array', 'min:1', 'max:100'],
            'task_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
