<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
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
            'income_category_id' => ['required', 'exists:income_categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'income_date' => ['required', 'date'],
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
            'income_category_id.required' => 'La catégorie est obligatoire.',
            'income_category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant doit être positif.',
            'income_date.required' => 'La date est obligatoire.',
            'income_date.date' => 'La date n\'est pas valide.',
            'recurrence_day.min' => 'Le jour doit être entre 1 et 31.',
            'recurrence_day.max' => 'Le jour doit être entre 1 et 31.',
        ];
    }
}
