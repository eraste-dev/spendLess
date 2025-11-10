<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'income_id' => ['sometimes', 'exists:incomes,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'income_date' => ['sometimes', 'date'],
            'is_recurring' => ['boolean'],
            'recurrence_day' => ['nullable', 'integer', 'min:1', 'max:31'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'income_id.exists' => 'Le revenu sélectionné n\'existe pas.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être positif.',
            'income_date.date' => 'La date n\'est pas valide.',
            'recurrence_day.min' => 'Le jour doit être entre 1 et 31.',
            'recurrence_day.max' => 'Le jour doit être entre 1 et 31.',
        ];
    }
}
