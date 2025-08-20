@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Order</h1>
            <p class="mt-1 text-sm text-gray-600">Add a new customer order</p>
        </div>
        <x-button href="{{ route('orders.index') }}" type="outline">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Orders
        </x-button>
    </div>

    <!-- Order Form -->
    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
            @csrf
            
            <div class="p-6 space-y-6">
                <!-- Customer and Payment Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer *</label>
                        <select name="customer_id" id="customer_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select a customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_method_id" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                        <select name="payment_method_id" id="payment_method_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select payment method</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Order Items -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-gray-700">Order Items *</label>
                        <button type="button" id="addItem" 
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add Item
                        </button>
                    </div>
                    
                    <div id="orderItems" class="space-y-3">
                        <!-- Order item template will be added here by JavaScript -->
                    </div>
                    @error('items')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Discount Codes -->
                <div>
                    <label for="discount_codes" class="block text-sm font-medium text-gray-700">Discount Codes (optional)</label>
                    <div class="mt-1 space-y-2" id="discountCodes">
                        <div class="flex">
                            <input type="text" name="discount_codes[]" placeholder="Enter discount code"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" onclick="addDiscountCode()" 
                                    class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                                Add
                            </button>
                        </div>
                    </div>
                    @error('discount_codes.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Any additional notes for this order...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Shipping Address -->
                <div>
                    <label for="shipping_address" class="block text-sm font-medium text-gray-700">Shipping Address</label>
                    <textarea name="shipping_address" id="shipping_address" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Enter shipping address...">{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <x-button href="{{ route('orders.index') }}" type="outline">Cancel</x-button>
                <x-button type="primary" submit="true">Create Order</x-button>
            </div>
        </form>
    </div>
</div>

<!-- Order Item Template -->
<template id="orderItemTemplate">
    <div class="order-item bg-gray-50 p-4 rounded-md">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Product</label>
                <select name="items[INDEX][product_id]" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->quantity_in_stock }}">
                            {{ $product->name }} - ${{ number_format($product->price, 2) }} (Stock: {{ $product->quantity_in_stock }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" name="items[INDEX][quantity]" min="1" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="button" onclick="removeOrderItem(this)"
                        class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                    Remove
                </button>
            </div>
        </div>
    </div>
</template>

<script>
let itemIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Add first item automatically
    addOrderItem();
    
    document.getElementById('addItem').addEventListener('click', addOrderItem);
});

function addOrderItem() {
    const template = document.getElementById('orderItemTemplate');
    const container = document.getElementById('orderItems');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX placeholder with actual index
    const html = clone.querySelector('.order-item').outerHTML.replace(/INDEX/g, itemIndex);
    container.insertAdjacentHTML('beforeend', html);
    
    itemIndex++;
}

function removeOrderItem(button) {
    if (document.querySelectorAll('.order-item').length > 1) {
        button.closest('.order-item').remove();
    } else {
        alert('At least one item is required.');
    }
}

function addDiscountCode() {
    const container = document.getElementById('discountCodes');
    const newInput = document.createElement('div');
    newInput.className = 'flex';
    newInput.innerHTML = `
        <input type="text" name="discount_codes[]" placeholder="Enter discount code"
               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <button type="button" onclick="removeDiscountCode(this)" 
                class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
            Remove
        </button>
    `;
    container.appendChild(newInput);
}

function removeDiscountCode(button) {
    button.closest('div').remove();
}
</script>
@endsection
