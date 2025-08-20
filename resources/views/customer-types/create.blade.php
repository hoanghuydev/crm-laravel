@extends('layouts.app')

@section('title', 'Add Customer Type')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mb-6">
                <h1 class="text-lg leading-6 font-medium text-gray-900">Add New Customer Type</h1>
                <p class="mt-1 text-sm text-gray-600">Create a new customer tier with specific benefits and discounts.</p>
            </div>

            <form method="POST" action="{{ route('customer-types.store') }}" class="space-y-6">
                @csrf
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Type Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
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
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
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
                               value="{{ old('discount_percentage', 0) }}" 
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
                               value="{{ old('min_order_amount', 0) }}" 
                               min="0" step="0.01" required
                               class="block w-full rounded-md border-gray-300 pl-7 focus:border-blue-500 focus:ring-blue-500 @error('min_order_amount') border-red-300 @enderror">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Minimum order amount to qualify for this tier benefits</p>
                    @error('min_order_amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Scoring Configuration -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Scoring Configuration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Minimum Score -->
                        <div>
                            <label for="minimum_score" class="block text-sm font-medium text-gray-700">
                                Minimum Score <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="minimum_score" name="minimum_score" 
                                   value="{{ old('minimum_score', 0) }}" 
                                   min="0" max="1" step="0.001" required
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('minimum_score') border-red-300 @enderror">
                            <p class="mt-1 text-sm text-gray-500">Score required to reach this tier (0.0 - 1.0)</p>
                            @error('minimum_score')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="priority" name="priority" 
                                   value="{{ old('priority', 1) }}" 
                                   min="1" max="100" required
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('priority') border-red-300 @enderror">
                            <p class="mt-1 text-sm text-gray-500">Higher number = higher priority (1-100)</p>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Custom Scoring Weights -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Custom Scoring Weights</h3>
                        <button type="button" id="toggle-weights" class="text-sm text-blue-600 hover:text-blue-500">
                            Use Default Weights
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Leave empty to use default weights. If specified, all weights must sum to 1.0</p>
                    
                    <div id="scoring-weights" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                        <!-- Total Value Weight -->
                        <div>
                            <label for="total_value_weight" class="block text-sm font-medium text-gray-700">
                                Total Value Weight
                            </label>
                            <input type="number" id="total_value_weight" name="scoring_weights[total_value_weight]" 
                                   value="{{ old('scoring_weights.total_value_weight') }}" 
                                   min="0" max="1" step="0.01" placeholder="0.35"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Weight for customer spending (default: 0.35)</p>
                        </div>

                        <!-- Order Count Weight -->
                        <div>
                            <label for="order_count_weight" class="block text-sm font-medium text-gray-700">
                                Order Count Weight
                            </label>
                            <input type="number" id="order_count_weight" name="scoring_weights[order_count_weight]" 
                                   value="{{ old('scoring_weights.order_count_weight') }}" 
                                   min="0" max="1" step="0.01" placeholder="0.25"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Weight for number of orders (default: 0.25)</p>
                        </div>

                        <!-- Order Frequency Weight -->
                        <div>
                            <label for="order_frequency_weight" class="block text-sm font-medium text-gray-700">
                                Order Frequency Weight
                            </label>
                            <input type="number" id="order_frequency_weight" name="scoring_weights[order_frequency_weight]" 
                                   value="{{ old('scoring_weights.order_frequency_weight') }}" 
                                   min="0" max="1" step="0.01" placeholder="0.25"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Weight for ordering frequency (default: 0.25)</p>
                        </div>

                        <!-- Location Weight -->
                        <div>
                            <label for="location_weight" class="block text-sm font-medium text-gray-700">
                                Location Weight
                            </label>
                            <input type="number" id="location_weight" name="scoring_weights[location_weight]" 
                                   value="{{ old('scoring_weights.location_weight') }}" 
                                   min="0" max="1" step="0.01" placeholder="0.15"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Weight for location (HCM priority) (default: 0.15)</p>
                        </div>
                    </div>
                    
                    <div id="weights-sum" class="mt-2 text-sm text-gray-600 hidden">
                        Total: <span id="sum-value">0.00</span> / 1.00
                    </div>
                    
                    @error('scoring_weights')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <x-button type="outline" href="{{ route('customer-types.index') }}">
                        Cancel
                    </x-button>
                    <x-button type="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Create Customer Type
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-weights');
    const scoringWeights = document.getElementById('scoring-weights');
    const weightsSum = document.getElementById('weights-sum');
    const sumValue = document.getElementById('sum-value');
    const weightInputs = document.querySelectorAll('#scoring-weights input[type="number"]');
    
    let weightsVisible = false;
    
    // Toggle weights visibility
    toggleButton.addEventListener('click', function() {
        weightsVisible = !weightsVisible;
        
        if (weightsVisible) {
            scoringWeights.classList.remove('hidden');
            weightsSum.classList.remove('hidden');
            toggleButton.textContent = 'Use Default Weights';
        } else {
            scoringWeights.classList.add('hidden');
            weightsSum.classList.add('hidden');
            toggleButton.textContent = 'Use Custom Weights';
            // Clear all weight inputs
            weightInputs.forEach(input => input.value = '');
            updateSum();
        }
    });
    
    // Calculate sum of weights
    function updateSum() {
        let total = 0;
        weightInputs.forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        
        sumValue.textContent = total.toFixed(2);
        
        // Color coding for validation
        if (Math.abs(total - 1.0) < 0.001 && total > 0) {
            sumValue.className = 'text-green-600 font-medium';
        } else if (total > 1.0) {
            sumValue.className = 'text-red-600 font-medium';
        } else {
            sumValue.className = 'text-gray-600';
        }
    }
    
    // Add event listeners to weight inputs
    weightInputs.forEach(input => {
        input.addEventListener('input', updateSum);
    });
    
    // Initial sum calculation
    updateSum();
    
    // If there are old values, show the weights section
    const hasOldWeights = Array.from(weightInputs).some(input => input.value !== '');
    if (hasOldWeights) {
        weightsVisible = true;
        scoringWeights.classList.remove('hidden');
        weightsSum.classList.remove('hidden');
        toggleButton.textContent = 'Use Default Weights';
        updateSum();
    }
});
</script>

@endsection
