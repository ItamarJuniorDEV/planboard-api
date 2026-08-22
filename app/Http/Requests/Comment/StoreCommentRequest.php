<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');
        $user = $this->user();

        return $task instanceof Task
            && $user !== null
            && $user->can('view', $task)
            && $user->can('create', Comment::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
        ];
    }
}
