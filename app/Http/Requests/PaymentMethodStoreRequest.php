<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:payment_methods,name',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Payment method name is required.',
            'name.unique' => 'This payment method name already exists.',
            'name.max' => 'Payment method name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'payment method name',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default active status if not provided
        if (!$this->has('is_active')) {
            $this->merge([
                'is_active' => true
            ]);
        }

        // Clean name - trim whitespace and title case
        if ($this->name) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }
    }
}
