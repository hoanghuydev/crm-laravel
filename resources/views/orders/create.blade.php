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

                <!-- Discount Codes with Stacking -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    
                    <div class="mt-1 space-y-2" id="discountCodes">
                        <div class="flex">
                            <input type="text" name="discount_codes[]" placeholder="Enter discount code (e.g. PRODUCT20)"
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   onchange="calculatePreview()">
                            <button type="button" onclick="addDiscountCode()" 
                                    class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3 text-xs text-blue-700">
                        <p><strong>Stacking Rules:</strong> Product ↔ Payment/Customer | Payment ↔ Product/Seasonal | Customer ↔ Product/Promotion</p>
                    </div>
                    
                    @error('discount_codes.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Order Preview -->
                <div class="bg-gray-50 p-4 rounded-lg" id="orderPreview" style="display: none;">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Order Preview</h3>
                    <div id="previewContent">
                        <!-- Preview will be populated by JavaScript -->
                    </div>
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
        <input type="text" name="discount_codes[]" placeholder="Enter discount code (e.g. PAYMENT5)"
               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
               onchange="calculatePreview()">
        <button type="button" onclick="removeDiscountCode(this)" 
                class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Remove
        </button>
    `;
    container.appendChild(newInput);
}

function removeDiscountCode(button) {
    button.closest('div').remove();
    calculatePreview();
}

// Real-time order calculation with discount stacking
function calculatePreview() {
    const customerId = document.getElementById('customer_id').value;
    const items = getOrderItems();
    const discountCodes = getDiscountCodes();

    if (!customerId || items.length === 0) {
        document.getElementById('orderPreview').style.display = 'none';
        return;
    }

    // Show loading state
    document.getElementById('orderPreview').style.display = 'block';
    document.getElementById('previewContent').innerHTML = '<div class="text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-sm text-gray-600">Calculating...</p></div>';

    fetch('{{ route("orders.preview-calculation") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            customer_id: customerId,
            items: items,
            discount_codes: discountCodes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPreview(data.data);
        } else {
            displayError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        displayError('Failed to calculate preview');
    });
}

function getOrderItems() {
    const items = [];
    const orderItems = document.querySelectorAll('.order-item');
    
    orderItems.forEach(item => {
        const productId = item.querySelector('select[name*="product_id"]').value;
        const quantity = item.querySelector('input[name*="quantity"]').value;
        
        if (productId && quantity) {
            items.push({
                product_id: parseInt(productId),
                quantity: parseInt(quantity)
            });
        }
    });
    
    return items;
}

function getDiscountCodes() {
    const codes = [];
    const inputs = document.querySelectorAll('input[name="discount_codes[]"]');
    
    inputs.forEach(input => {
        if (input.value.trim()) {
            codes.push(input.value.trim());
        }
    });
    
    return codes;
}

function displayPreview(data) {
    const stackingResult = data.discount_stacking_result;
    
    let html = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Order Summary</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>${formatCurrency(data.subtotal)}</span>
                    </div>`;
    
    if (data.customer_discount_amount > 0) {
        html += `
                    <div class="flex justify-between text-green-600">
                        <span>Customer Discount (${data.customer_discount_percentage}%):</span>
                        <span>-${formatCurrency(data.customer_discount_amount)}</span>
                    </div>`;
    }
    
    if (data.discount_amount > 0) {
        html += `
                    <div class="flex justify-between text-green-600">
                        <span>Code Discounts:</span>
                        <span>-${formatCurrency(data.discount_amount)}</span>
                    </div>`;
    }
    
    html += `
                    <div class="border-t pt-2 flex justify-between font-bold">
                        <span>Total:</span>
                        <span>${formatCurrency(data.total)}</span>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Discount Stacking</h4>`;
    
    if (stackingResult.applied_discounts && stackingResult.applied_discounts.length > 0) {
        html += '<div class="space-y-2">';
        stackingResult.applied_discounts.forEach(applied => {
            const stackedWith = applied.stacked_with ? ` (stacked with ${applied.stacked_with})` : '';
            html += `
                <div class="flex items-center justify-between p-2 bg-green-100 rounded">
                    <div>
                        <div class="text-sm font-medium text-green-800">${applied.discount.code}</div>
                        <div class="text-xs text-green-600">${applied.category}${stackedWith}</div>
                    </div>
                    <div class="text-sm font-bold text-green-800">-${formatCurrency(applied.amount)}</div>
                </div>`;
        });
        html += '</div>';
    }
    
    if (stackingResult.stacking_conflicts && stackingResult.stacking_conflicts.length > 0) {
        html += '<div class="mt-3"><h5 class="text-sm font-medium text-red-800 mb-2">Conflicts:</h5><div class="space-y-1">';
        stackingResult.stacking_conflicts.forEach(conflict => {
            html += `
                <div class="p-2 bg-red-100 rounded">
                    <div class="text-sm font-medium text-red-800">${conflict.discount.code}</div>
                    <div class="text-xs text-red-600">${conflict.reason}</div>
                </div>`;
        });
        html += '</div></div>';
    }
    
    html += '</div></div>';
    
    document.getElementById('previewContent').innerHTML = html;
}

function displayError(message) {
    document.getElementById('previewContent').innerHTML = `
        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <div class="flex">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>${message}</span>
            </div>
        </div>`;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND',
        minimumFractionDigits: 0
    }).format(amount);
}

// Add event listeners for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Listen for customer changes
    document.getElementById('customer_id').addEventListener('change', calculatePreview);
    
    // Listen for item changes (using event delegation)
    document.getElementById('orderItems').addEventListener('change', calculatePreview);
});
</script>
@endsection
