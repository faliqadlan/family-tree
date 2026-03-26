<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        $isStub = $this->boolean('is_stub');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => [$isStub ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['sometimes', 'in:user,member,admin'],
            'is_stub' => ['sometimes', 'boolean'],
            'is_deceased' => ['sometimes', 'boolean'],
            'email_verified_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
