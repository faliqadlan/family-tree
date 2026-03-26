<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'date_of_death' => ['nullable', 'date'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'phone_privacy' => ['sometimes', 'in:public,private,masked'],
            'email_privacy' => ['sometimes', 'in:public,private,masked'],
            'dob_privacy' => ['sometimes', 'in:public,private,masked'],
            'address' => ['nullable', 'string'],
            'address_privacy' => ['sometimes', 'in:public,private,masked'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'graph_node_id' => ['nullable', 'uuid'],
        ];
    }
}
