<?php

namespace App\Http\Requests\Milestone;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $user = $this->user();

        return $project instanceof Project
            && $user !== null
            && $user->can('view', $project)
            && $user->can('create', Milestone::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
