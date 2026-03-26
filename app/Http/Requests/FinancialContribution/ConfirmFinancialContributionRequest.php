<?php

namespace App\Http\Requests\FinancialContribution;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmFinancialContributionRequest extends FormRequest
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
            'status' => ['sometimes', 'in:confirmed,rejected'],
        ];
    }
}
