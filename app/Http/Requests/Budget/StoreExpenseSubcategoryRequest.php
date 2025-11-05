<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseSubcategoryRequest extends FormRequest
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
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'expense_category_id.required' => 'La catégorie parente est requise.',
            'expense_category_id.exists' => 'La catégorie parente sélectionnée n\'existe pas.',
            'name.required' => 'Le nom de la sous-catégorie est requis.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'target_amount.numeric' => 'Le montant cible doit être un nombre.',
            'target_amount.min' => 'Le montant cible doit être positif.',
            'order.integer' => 'L\'ordre doit être un nombre entier.',
            'order.min' => 'L\'ordre doit être un nombre positif.',
        ];
    }
}
