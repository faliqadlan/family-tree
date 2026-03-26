<?php

namespace App\Http\Requests\FinancialContribution;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialContributionRequest extends FormRequest
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
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'payment_method' => ['sometimes', 'in:transfer,cash,other'],
            'status' => ['sometimes', 'in:pending,confirmed,rejected'],
            'reference_number' => ['sometimes', 'nullable', 'string', 'max:100'],
            'note' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
