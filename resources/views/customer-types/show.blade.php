@extends('layouts.app')

@section('title', $customerType->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $customerType->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">Customer type details and statistics</p>
        </div>
        <div class="flex space-x-3">
            <x-button type="outline" href="{{ route('customer-types.edit', $customerType) }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </x-button>
            <x-button type="outline" href="{{ route('customer-types.index') }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Types
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Customer Type Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Type Information</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customerType->name }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $customerType->description ?: 'No description provided' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Discount Percentage</dt>
                            <dd class="mt-1">
                                @if($customerType->discount_percentage > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $customerType->discount_percentage }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">No discount</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Minimum Order Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($customerType->min_order_amount > 0)
                                    ${{ number_format($customerType->min_order_amount, 2) }}
                                @else
                                    <span class="text-gray-400">No minimum</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($customerType->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $customerType->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Scoring Configuration -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Scoring Configuration</h3>
                    <p class="mt-1 text-sm text-gray-600">Customer scoring thresholds and weights for this tier</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Minimum Score Required</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    ≥ {{ number_format($customerType->minimum_score ?? 0, 3) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Priority Level</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    Level {{ $customerType->priority ?? 1 }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">Higher number = higher priority</p>
                            </dd>
                        </div>
                        
                        @php
                            $weights = $customerType->getScoringWeights();
                            $isCustomWeights = !empty($customerType->scoring_weights);
                        @endphp
                        
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">
                                Scoring Weights 
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $isCustomWeights ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $isCustomWeights ? 'Custom' : 'Default' }}
                                </span>
                            </dt>
                            <dd class="mt-2">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm font-medium text-gray-900">Total Value</div>
                                        <div class="text-lg font-bold text-blue-600">{{ number_format($weights['total_value_weight'] * 100, 1) }}%</div>
                                        <div class="text-xs text-gray-500">Customer spending weight</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm font-medium text-gray-900">Order Count</div>
                                        <div class="text-lg font-bold text-green-600">{{ number_format($weights['order_count_weight'] * 100, 1) }}%</div>
                                        <div class="text-xs text-gray-500">Number of orders weight</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm font-medium text-gray-900">Frequency</div>
                                        <div class="text-lg font-bold text-orange-600">{{ number_format($weights['order_frequency_weight'] * 100, 1) }}%</div>
                                        <div class="text-xs text-gray-500">Order frequency weight</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm font-medium text-gray-900">Location</div>
                                        <div class="text-lg font-bold text-purple-600">{{ number_format($weights['location_weight'] * 100, 1) }}%</div>
                                        <div class="text-xs text-gray-500">HCM location weight</div>
                                    </div>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Stats Sidebar -->
        <div class="space-y-6">
            <!-- Customer Stats -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-4">
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $customerType->customers_count }}</div>
                        <div class="text-sm text-gray-500">Total Customers</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $customerType->customers()->where('is_active', true)->count() }}
                        </div>
                        <div class="text-sm text-gray-500">Active Customers</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('customers.index', ['customer_type' => $customerType->id]) }}" 
                       class="w-full text-left px-3 py-2 text-sm text-blue-700 hover:bg-blue-50 rounded-md block">
                        View Customers
                    </a>
                    @if($customerType->is_active)
                        <form method="POST" action="{{ route('customer-types.destroy', $customerType) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-700 hover:bg-red-50 rounded-md"
                                    onclick="return confirm('Are you sure you want to deactivate this customer type?')">
                                Deactivate Type
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
