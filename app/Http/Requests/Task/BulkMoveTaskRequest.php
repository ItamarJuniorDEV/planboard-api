<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class BulkMoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Task::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', 'distinct'],
            'column_id' => ['required', 'integer'],
        ];
    }
}
