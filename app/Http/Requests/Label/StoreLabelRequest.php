<?php

namespace App\Http\Requests\Label;

use App\Models\Label;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $user = $this->user();

        return $project instanceof Project
            && $user !== null
            && $user->can('view', $project)
            && $user->can('create', Label::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:30'],
        ];
    }
}
