<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug|regex:/^[a-z0-9\-]+$/',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999999.99',
            'quantity_in_stock' => 'required|integer|min:0|max:999999',
            'sku' => 'nullable|string|max:100|unique:products,sku|regex:/^[A-Z0-9\-_]+$/i',
            'image_url' => 'nullable|url|max:500',
            'status' => 'required|in:active,inactive,out_of_stock',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'description.max' => 'Product description cannot exceed 1000 characters.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price cannot exceed 999,999,999.99.',
            'quantity_in_stock.required' => 'Stock quantity is required.',
            'quantity_in_stock.integer' => 'Stock quantity must be a whole number.',
            'quantity_in_stock.min' => 'Stock quantity cannot be negative.',
            'quantity_in_stock.max' => 'Stock quantity cannot exceed 999,999.',
            'sku.unique' => 'This SKU already exists.',
            'slug.unique' => 'This slug already exists.',
            'slug.max' => 'Slug cannot exceed 255 characters.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'sku.max' => 'SKU cannot exceed 100 characters.',
            'sku.regex' => 'SKU can only contain letters, numbers, hyphens, and underscores.',
            'image_url.url' => 'Image URL must be a valid URL.',
            'image_url.max' => 'Image URL cannot exceed 500 characters.',
            'status.required' => 'Product status is required.',
            'status.in' => 'Product status must be active, inactive, or out of stock.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'product name',
            'slug' => 'slug',
            'description' => 'product description',
            'price' => 'price',
            'quantity_in_stock' => 'stock quantity',
            'sku' => 'SKU',
            'image_url' => 'image URL',
            'status' => 'status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and lowercase slug
        if ($this->slug) {
            $this->merge([
                'slug' => strtolower(trim($this->slug))
            ]);
        }

        // Clean and uppercase SKU
        if ($this->sku) {
            $this->merge([
                'sku' => strtoupper(trim($this->sku))
            ]);
        }

        // Ensure price has proper decimal format
        if ($this->price) {
            $this->merge([
                'price' => number_format((float)$this->price, 2, '.', '')
            ]);
        }
    }
}
