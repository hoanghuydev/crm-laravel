@extends('layouts.app')

@section('title', 'Edit Order #' . $order->order_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Order #{{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-gray-600">Update order details and status</p>
        </div>
        <div class="flex space-x-3">
            <x-button href="{{ route('orders.show', $order) }}" type="outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View Order
            </x-button>
            <x-button href="{{ route('orders.index') }}" type="outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Orders
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Edit Form -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <form method="POST" action="{{ route('orders.update', $order) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 space-y-6">
                        <!-- Order Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Order Status</label>
                            <select name="status" id="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $order->status == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Note: Some status transitions may not be allowed based on current status.
                            </p>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label for="payment_method_id" class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method_id" id="payment_method_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}" {{ $order->payment_method_id == $method->id ? 'selected' : '' }}>
                                        {{ $method->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="4" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Any additional notes for this order...">{{ old('notes', $order->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Shipping Address -->
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700">Shipping Address</label>
                            <textarea name="shipping_address" id="shipping_address" rows="4" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Enter shipping address...">{{ old('shipping_address', $order->shipping_address) }}</textarea>
                            @error('shipping_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <x-button href="{{ route('orders.show', $order) }}" type="outline">Cancel</x-button>
                        <x-button type="primary" submit="true">Update Order</x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Number:</span>
                        <span class="font-medium">{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Date:</span>
                        <span class="text-sm">{{ $order->order_date->format('M j, Y g:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Current Status:</span>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-blue-100 text-blue-800',
                                'processing' => 'bg-indigo-100 text-indigo-800',
                                'shipped' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-medium text-lg">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer</h2>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-white font-medium">
                                    {{ substr($order->customer->name, 0, 1) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</div>
                            <div class="text-sm text-gray-500">{{ $order->customer->customerType->name }}</div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">
                        <div>{{ $order->customer->email }}</div>
                        @if($order->customer->phone)
                            <div>{{ $order->customer->phone }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
                <div class="space-y-3">
                    @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                <div class="text-xs text-gray-500">
                                    ${{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}
                                </div>
                            </div>
                            <div class="text-sm font-medium text-gray-900">
                                ${{ number_format($item->total_price, 2) }}
                            </div>
                        </div>
                    @endforeach
                    
                    @if($order->getTotalDiscountAmount() > 0)
                        <hr class="my-2">
                        <div class="space-y-1">
                            @if($order->customer_discount_amount > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Customer Discount:</span>
                                    <span>-${{ number_format($order->customer_discount_amount, 2) }}</span>
                                </div>
                            @endif
                            @if($order->discount_amount > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Promotional Discounts:</span>
                                    <span>-${{ number_format($order->discount_amount, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <hr class="my-2">
                    <div class="flex justify-between font-medium">
                        <span>Total:</span>
                        <span>${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Warning -->
            @if($order->status === 'cancelled' || $order->status === 'delivered')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Limited Editing</h3>
                            <p class="mt-1 text-sm text-yellow-700">
                                This order is {{ $order->status }}. Only notes and shipping address can be updated.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
