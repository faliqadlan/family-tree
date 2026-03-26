<?php

namespace App\Http\Requests\AccessRequest;

use Illuminate\Foundation\Http\FormRequest;

class RespondAccessRequestRequest extends FormRequest
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
            'status' => ['required', 'in:approved,rejected,revoked'],
            'target_response' => ['nullable', 'string', 'max:500'],
        ];
    }
}
