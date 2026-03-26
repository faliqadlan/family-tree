<?php

namespace App\Http\Requests\AccessRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccessRequestRequest extends FormRequest
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
            'target_id' => ['required', 'exists:users,id', Rule::notIn([$this->user()?->id])],
            'requested_fields' => ['required', 'array', 'min:1'],
            'requested_fields.*' => ['in:phone,email,address,date_of_birth'],
            'requester_message' => ['nullable', 'string', 'max:500'],
        ];
    }
}
