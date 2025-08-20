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
        $customerTypeId = $this->route('customer_type')->id ?? $this->route('customer_type');
        
        return [
            'name' => 'required|string|max:100|unique:customer_types,name,' . $customerTypeId,
            'description' => 'nullable|string|max:500',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'min_order_amount' => 'required|numeric|min:0',
            'minimum_score' => 'required|numeric|min:0|max:1',
            'priority' => 'required|integer|min:1|max:100',
            'scoring_weights' => 'nullable|array',
            'scoring_weights.total_value_weight' => 'nullable|numeric|min:0|max:1',
            'scoring_weights.order_count_weight' => 'nullable|numeric|min:0|max:1',
            'scoring_weights.order_frequency_weight' => 'nullable|numeric|min:0|max:1',
            'scoring_weights.location_weight' => 'nullable|numeric|min:0|max:1',
            'is_active' => 'boolean',
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
            'minimum_score.required' => 'Minimum score is required.',
            'minimum_score.numeric' => 'Minimum score must be a number.',
            'minimum_score.min' => 'Minimum score cannot be negative.',
            'minimum_score.max' => 'Minimum score cannot exceed 1.0.',
            'priority.required' => 'Priority is required.',
            'priority.integer' => 'Priority must be an integer.',
            'priority.min' => 'Priority must be at least 1.',
            'priority.max' => 'Priority cannot exceed 100.',
            'scoring_weights.*.numeric' => 'Scoring weights must be numeric.',
            'scoring_weights.*.min' => 'Scoring weights cannot be negative.',
            'scoring_weights.*.max' => 'Scoring weights cannot exceed 1.0.',
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
            'minimum_score' => 'minimum score',
            'priority' => 'priority',
            'scoring_weights.total_value_weight' => 'total value weight',
            'scoring_weights.order_count_weight' => 'order count weight',
            'scoring_weights.order_frequency_weight' => 'order frequency weight',
            'scoring_weights.location_weight' => 'location weight',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values for new fields if not provided
        if (!$this->has('minimum_score')) {
            $this->merge(['minimum_score' => 0]);
        }
        
        if (!$this->has('priority')) {
            $this->merge(['priority' => 1]);
        }

        // Remove empty scoring weights to use defaults
        if ($this->has('scoring_weights')) {
            $weights = array_filter($this->scoring_weights ?? [], function($value) {
                return $value !== null && $value !== '';
            });
            
            if (empty($weights)) {
                $this->request->remove('scoring_weights');
            } else {
                $this->merge(['scoring_weights' => $weights]);
            }
        }
        
        // Set default values for boolean fields
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate scoring weights sum to 1.0 if provided
            if ($this->has('scoring_weights') && !empty($this->scoring_weights)) {
                $weights = $this->scoring_weights;
                $totalWeight = array_sum([
                    $weights['total_value_weight'] ?? 0,
                    $weights['order_count_weight'] ?? 0,
                    $weights['order_frequency_weight'] ?? 0,
                    $weights['location_weight'] ?? 0,
                ]);

                if (abs($totalWeight - 1.0) > 0.001) { // Allow small floating point differences
                    $validator->errors()->add('scoring_weights', 'The sum of all scoring weights must equal 1.0');
                }
            }
        });
    }
}
