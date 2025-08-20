@extends('layouts.app')

@section('title', 'Edit Customer Type - ' . $customerType->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mb-6">
                <h1 class="text-lg leading-6 font-medium text-gray-900">Edit Customer Type</h1>
                <p class="mt-1 text-sm text-gray-600">Update customer tier information and benefits.</p>
            </div>

            <form method="POST" action="{{ route('customer-types.update', $customerType) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Type Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $customerType->name) }}" required
                           placeholder="e.g., VIP, Premium, Gold"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              placeholder="Describe the benefits and features of this customer type"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror">{{ old('description', $customerType->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Discount Percentage -->
                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700">
                        Discount Percentage <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="number" id="discount_percentage" name="discount_percentage" 
                               value="{{ old('discount_percentage', $customerType->discount_percentage) }}" 
                               min="0" max="100" step="0.01" required
                               class="block w-full rounded-md border-gray-300 pr-12 focus:border-blue-500 focus:ring-blue-500 @error('discount_percentage') border-red-300 @enderror">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Set to 0 for no discount</p>
                    @error('discount_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Minimum Order Amount -->
                <div>
                    <label for="min_order_amount" class="block text-sm font-medium text-gray-700">
                        Minimum Order Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" id="min_order_amount" name="min_order_amount" 
                               value="{{ old('min_order_amount', $customerType->min_order_amount) }}" 
                               min="0" step="0.01" required
                               class="block w-full rounded-md border-gray-300 pl-7 focus:border-blue-500 focus:ring-blue-500 @error('min_order_amount') border-red-300 @enderror">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Minimum order amount to qualify for this tier benefits</p>
                    @error('min_order_amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <x-button type="outline" href="{{ route('customer-types.show', $customerType) }}">
                        Cancel
                    </x-button>
                    <x-button type="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Customer Type
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
