<?php

namespace App\Http\Requests\FinancialContribution;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialContributionRequest extends FormRequest
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
            'event_id' => ['required', 'exists:events,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'],
            'payment_method' => ['required', 'in:transfer,cash,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
        ];
    }
}
