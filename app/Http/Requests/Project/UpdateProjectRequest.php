<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'budget' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:draft,planning,active,on_hold,completed,cancelled'],
            'deadline' => ['nullable', 'date'],
        ];
    }
}
