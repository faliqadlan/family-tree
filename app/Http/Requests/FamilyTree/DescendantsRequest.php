<?php

namespace App\Http\Requests\FamilyTree;

use Illuminate\Foundation\Http\FormRequest;

class DescendantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ancestor_uuid' => ['required', 'uuid'],
            'depth' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
