<?php

namespace App\Http\Requests\AccessRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccessRequestRequest extends FormRequest
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
            'requested_fields' => ['sometimes', 'array', 'min:1'],
            'requested_fields.*' => ['in:phone,email,address,date_of_birth'],
            'requester_message' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
