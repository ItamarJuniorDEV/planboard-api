<?php

namespace App\Http\Requests\Column;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Foundation\Http\FormRequest;

class IndexColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        $board = $this->route('board');
        $user = $this->user();

        return $board instanceof Board
            && $user !== null
            && $user->can('view', $board)
            && $user->can('viewAny', Column::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
