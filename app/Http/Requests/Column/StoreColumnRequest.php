<?php

namespace App\Http\Requests\Column;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Foundation\Http\FormRequest;

class StoreColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        $board = $this->route('board');
        $user = $this->user();

        return $board instanceof Board
            && $user !== null
            && $user->can('view', $board)
            && $user->can('create', Column::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'integer', 'min:1'],
        ];
    }
}
