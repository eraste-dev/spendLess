<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_recurring_monthly' => ['nullable', 'boolean'],
            'recurrence_day' => ['nullable', 'integer', 'min:1', 'max:31'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la catégorie est requis.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'target_percentage.numeric' => 'Le pourcentage cible doit être un nombre.',
            'target_percentage.min' => 'Le pourcentage cible doit être positif.',
            'target_percentage.max' => 'Le pourcentage cible ne peut pas dépasser 100%.',
            'target_amount.numeric' => 'Le montant cible doit être un nombre.',
            'target_amount.min' => 'Le montant cible doit être positif.',
            'color.regex' => 'Le format de la couleur doit être hexadécimal (ex: #8b5cf6).',
            'order.integer' => 'L\'ordre doit être un nombre entier.',
            'order.min' => 'L\'ordre doit être un nombre positif.',
            'recurrence_day.min' => 'Le jour de récurrence doit être entre 1 et 31.',
            'recurrence_day.max' => 'Le jour de récurrence doit être entre 1 et 31.',
        ];
    }
}
