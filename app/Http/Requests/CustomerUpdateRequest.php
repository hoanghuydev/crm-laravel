<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerUpdateRequest extends FormRequest
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
        $customerId = $this->route('customer');
        
        return [
            'customer_type_id' => 'required|exists:customer_types,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email,' . $customerId,
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'customer_type_id.required' => 'Customer type is required.',
            'customer_type_id.exists' => 'Selected customer type is invalid.',
            'name.required' => 'Customer name is required.',
            'name.max' => 'Customer name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Please enter a valid phone number.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'date_of_birth.date' => 'Please enter a valid date.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'gender.in' => 'Gender must be male, female, or other.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'customer_type_id' => 'customer type',
            'name' => 'customer name',
            'email' => 'email address',
            'phone' => 'phone number',
            'address' => 'address',
            'date_of_birth' => 'date of birth',
            'gender' => 'gender',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone number - remove spaces and special characters except +
        if ($this->phone) {
            $this->merge([
                'phone' => preg_replace('/[^\+\d]/', '', $this->phone)
            ]);
        }
    }
}
