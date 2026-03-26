<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'full_name' => ['sometimes', 'string', 'max:255'],
            'nickname' => ['sometimes', 'nullable', 'string', 'max:100'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'date_of_death' => ['sometimes', 'nullable', 'date'],
            'place_of_birth' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'phone_privacy' => ['sometimes', 'in:public,private,masked'],
            'email_privacy' => ['sometimes', 'in:public,private,masked'],
            'dob_privacy' => ['sometimes', 'in:public,private,masked'],
            'address' => ['sometimes', 'nullable', 'string'],
            'address_privacy' => ['sometimes', 'in:public,private,masked'],
            'father_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mother_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
