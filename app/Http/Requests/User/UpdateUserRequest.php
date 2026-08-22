<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $target = $this->route('user');
        $userId = $target instanceof User ? $target->id : null;
        $isSelf = $target instanceof User && $this->user()?->id === $target->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email,'.$userId],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'in:admin,member', Rule::prohibitedIf($isSelf)],
        ];
    }
}
