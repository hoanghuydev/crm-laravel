<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerTypeUpdateRequest extends FormRequest
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
        $customerTypeId = $this->route('customer_type');
        
        return [
            'name' => 'required|string|max:100|unique:customer_types,name,' . $customerTypeId,
            'description' => 'nullable|string|max:500',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'min_order_amount' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Customer type name is required.',
            'name.unique' => 'This customer type name already exists.',
            'name.max' => 'Customer type name cannot exceed 100 characters.',
            'discount_percentage.required' => 'Discount percentage is required.',
            'discount_percentage.numeric' => 'Discount percentage must be a number.',
            'discount_percentage.min' => 'Discount percentage cannot be negative.',
            'discount_percentage.max' => 'Discount percentage cannot exceed 100%.',
            'min_order_amount.required' => 'Minimum order amount is required.',
            'min_order_amount.numeric' => 'Minimum order amount must be a number.',
            'min_order_amount.min' => 'Minimum order amount cannot be negative.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'customer type name',
            'discount_percentage' => 'discount percentage',
            'min_order_amount' => 'minimum order amount',
        ];
    }
}
