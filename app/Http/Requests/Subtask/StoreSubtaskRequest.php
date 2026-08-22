<?php

namespace App\Http\Requests\Subtask;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');
        $user = $this->user();

        return $task instanceof Task
            && $user !== null
            && $user->can('view', $task)
            && $user->can('create', Subtask::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'done' => ['required', 'boolean'],
        ];
    }
}
