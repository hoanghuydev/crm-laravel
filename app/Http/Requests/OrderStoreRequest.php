<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'discount_codes' => 'sometimes|array',
            'discount_codes.*' => 'string|max:50',
            'notes' => 'nullable|string|max:1000',
            'shipping_address' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer is invalid.',
            'payment_method_id.required' => 'Payment method is required.',
            'payment_method_id.exists' => 'Selected payment method is invalid.',
            'items.required' => 'At least one product is required.',
            'items.min' => 'At least one product is required.',
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'Selected product is invalid.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.integer' => 'Quantity must be a valid number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity cannot exceed 999.',
            'discount_codes.array' => 'Discount codes must be an array.',
            'discount_codes.*.string' => 'Each discount code must be a valid string.',
            'discount_codes.*.max' => 'Discount code cannot exceed 50 characters.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'shipping_address.max' => 'Shipping address cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'customer',
            'payment_method_id' => 'payment method',
            'items' => 'products',
            'items.*.product_id' => 'product',
            'items.*.quantity' => 'quantity',
            'discount_codes' => 'discount codes',
            'notes' => 'notes',
            'shipping_address' => 'shipping address',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up items array - remove empty entries
        if ($this->has('items')) {
            $items = array_filter($this->items, function($item) {
                return !empty($item['product_id']) && !empty($item['quantity']);
            });
            $this->merge(['items' => array_values($items)]);
        }

        // Clean up discount codes - remove empty entries
        if ($this->has('discount_codes')) {
            $discountCodes = array_filter($this->discount_codes, function($code) {
                return !empty(trim($code));
            });
            $this->merge(['discount_codes' => array_values($discountCodes)]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation can be added here
            // For example, checking if customer is active
            if ($this->customer_id) {
                $customer = \App\Models\Customer::find($this->customer_id);
                if ($customer && !$customer->is_active) {
                    $validator->errors()->add('customer_id', 'Selected customer is not active.');
                }
            }

            // Check if payment method is active
            if ($this->payment_method_id) {
                $paymentMethod = \App\Models\PaymentMethod::find($this->payment_method_id);
                if ($paymentMethod && !$paymentMethod->is_active) {
                    $validator->errors()->add('payment_method_id', 'Selected payment method is not active.');
                }
            }
        });
    }
}
