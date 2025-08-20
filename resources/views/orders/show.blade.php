@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-gray-600">Placed on {{ $order->order_date->format('F j, Y \a\t g:i A') }}</p>
        </div>
        <div class="flex space-x-3">
            @if($order->status !== 'cancelled' && $order->status !== 'delivered')
                <x-button href="{{ route('orders.edit', $order) }}" type="outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Order
                </x-button>
            @endif
            <x-button href="{{ route('orders.index') }}" type="outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Orders
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Status and Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Order Status</h2>
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
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                @if($order->status !== 'cancelled' && $order->status !== 'delivered')
                    <div class="space-y-3">
                        <p class="text-sm text-gray-600">Update order status:</p>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $nextStatuses = [
                                    'pending' => ['confirmed'],
                                    'confirmed' => ['processing'],
                                    'processing' => ['shipped'],
                                    'shipped' => ['delivered']
                                ];
                            @endphp
                            
                            @if(isset($nextStatuses[$order->status]))
                                @foreach($nextStatuses[$order->status] as $nextStatus)
                                    <form method="POST" action="{{ route('orders.update-status', $order) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700">
                                            Mark as {{ ucfirst($nextStatus) }}
                                        </button>
                                    </form>
                                @endforeach
                            @endif

                            @if($order->canBeCancelled())
                                <form method="POST" action="{{ route('orders.cancel', $order) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            onclick="return confirm('Are you sure you want to cancel this order?')"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">
                                        Cancel Order
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Status Timeline -->
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Status Timeline</h3>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"></div>
                            <span class="ml-3 text-gray-600">Order placed</span>
                            <span class="ml-auto text-gray-500">{{ $order->order_date->format('M j, Y g:i A') }}</span>
                        </div>
                        @if($order->shipped_date)
                            <div class="flex items-center text-sm">
                                <div class="flex-shrink-0 w-2 h-2 bg-purple-600 rounded-full"></div>
                                <span class="ml-3 text-gray-600">Order shipped</span>
                                <span class="ml-auto text-gray-500">{{ $order->shipped_date->format('M j, Y g:i A') }}</span>
                            </div>
                        @endif
                        @if($order->delivered_date)
                            <div class="flex items-center text-sm">
                                <div class="flex-shrink-0 w-2 h-2 bg-green-600 rounded-full"></div>
                                <span class="ml-3 text-gray-600">Order delivered</span>
                                <span class="ml-auto text-gray-500">{{ $order->delivered_date->format('M j, Y g:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        @if($item->product->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($item->product->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($item->total_price, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Applied Discounts -->
            @if($order->orderDiscounts->count() > 0)
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Applied Discounts</h2>
                    <div class="space-y-3">
                        @foreach($order->orderDiscounts as $orderDiscount)
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-md">
                                <div>
                                    <div class="font-medium text-green-900">{{ $orderDiscount->discount->code }}</div>
                                    <div class="text-sm text-green-700">{{ $orderDiscount->discount->description }}</div>
                                </div>
                                <div class="text-green-900 font-medium">
                                    -${{ number_format($orderDiscount->discount_amount, 2) }}
                                </div>
                            </div>
                        @endforeach
                        @if($order->customer_discount_amount > 0)
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-md">
                                <div>
                                    <div class="font-medium text-blue-900">Customer Type Discount</div>
                                    <div class="text-sm text-blue-700">{{ $order->customer->customerType->name }} ({{ $order->customer->customerType->discount_percentage }}%)</div>
                                </div>
                                <div class="text-blue-900 font-medium">
                                    -${{ number_format($order->customer_discount_amount, 2) }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($order->notes)
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Notes</h2>
                    <p class="text-gray-700">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
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
                        <div>Email: {{ $order->customer->email }}</div>
                        @if($order->customer->phone)
                            <div>Phone: {{ $order->customer->phone }}</div>
                        @endif
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('customers.show', $order->customer) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Customer Profile →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="font-medium">{{ $order->paymentMethod->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->customer_discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Customer Discount:</span>
                            <span>-${{ number_format($order->customer_discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Promotional Discounts:</span>
                            <span>-${{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <hr>
                    <div class="flex justify-between text-lg font-semibold">
                        <span>Total:</span>
                        <span>${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            @if($order->shipping_address)
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h2>
                    <p class="text-gray-700">{{ $order->shipping_address }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
