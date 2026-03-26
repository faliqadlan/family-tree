<?php

namespace App\Http\Requests\Rsvp;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRsvpRequest extends FormRequest
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
            'status' => ['sometimes', 'in:pending,attending,not_attending,maybe'],
            'pax' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'note' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
