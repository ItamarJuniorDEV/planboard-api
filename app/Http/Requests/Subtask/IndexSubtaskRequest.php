<?php

namespace App\Http\Requests\Subtask;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class IndexSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');
        $user = $this->user();

        return $task instanceof Task
            && $user !== null
            && $user->can('view', $task)
            && $user->can('viewAny', Subtask::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'per_page' => ['integer', 'nullable', 'min:1', 'max:50'],
        ];
    }
}
