<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
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
            'status' => 'sometimes|string|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'notes' => 'sometimes|nullable|string|max:1000',
            'shipping_address' => 'sometimes|nullable|string|max:500',
            'payment_method_id' => 'sometimes|exists:payment_methods,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: pending, confirmed, processing, shipped, delivered, or cancelled.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'shipping_address.max' => 'Shipping address cannot exceed 500 characters.',
            'payment_method_id.exists' => 'Selected payment method is invalid.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'status' => 'order status',
            'notes' => 'notes',
            'shipping_address' => 'shipping address',
            'payment_method_id' => 'payment method',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');
            
            // Check if status transition is valid
            if ($this->has('status') && $order) {
                $currentStatus = $order->status;
                $newStatus = $this->status;
                
                // Define valid status transitions
                $validTransitions = [
                    'pending' => ['confirmed', 'cancelled'],
                    'confirmed' => ['processing', 'cancelled'],
                    'processing' => ['shipped', 'cancelled'],
                    'shipped' => ['delivered'],
                    'delivered' => [], // Final status
                    'cancelled' => [], // Final status
                ];
                
                if (isset($validTransitions[$currentStatus]) && 
                    !in_array($newStatus, $validTransitions[$currentStatus])) {
                    $validator->errors()->add('status', 
                        "Cannot change status from {$currentStatus} to {$newStatus}.");
                }
            }

            // Check if payment method is active (if being updated)
            if ($this->has('payment_method_id') && $this->payment_method_id) {
                $paymentMethod = \App\Models\PaymentMethod::find($this->payment_method_id);
                if ($paymentMethod && !$paymentMethod->is_active) {
                    $validator->errors()->add('payment_method_id', 'Selected payment method is not active.');
                }
            }
        });
    }
}
