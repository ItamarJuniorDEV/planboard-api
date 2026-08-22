<?php

namespace App\Http\Requests\Board;

use App\Models\Board;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreBoardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $user = $this->user();

        return $project instanceof Project
            && $user !== null
            && $user->can('view', $project)
            && $user->can('create', Board::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,archived'],
        ];
    }
}
