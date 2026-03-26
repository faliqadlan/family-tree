<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date'],
            'ancestor_node_id' => ['sometimes', 'nullable', 'uuid'],
            'invitation_depth' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10'],
            'status' => ['sometimes', 'in:draft,published,cancelled,completed'],
        ];
    }
}
