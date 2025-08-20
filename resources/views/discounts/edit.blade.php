@extends('layouts.app')

@section('title', 'Chỉnh sửa Discount')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Chỉnh sửa Discount</h1>
                    <p class="text-sm text-gray-600 mt-1">Cập nhật thông tin discount "{{ $discount->code }}"</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('discounts.show', $discount) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Xem chi tiết
                    </a>
                    <a href="{{ route('discounts.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Quay lại
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('discounts.update', $discount) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Thông tin cơ bản</h3>
                        
                        <!-- Code -->
                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                Mã giảm giá <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="code" 
                                   id="code"
                                   value="{{ old('code', $discount->code) }}"
                                   placeholder="VD: SAVE10, WELCOME20"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror">
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Tên chương trình <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name', $discount->name) }}"
                                   placeholder="VD: Giảm giá cuối tuần"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Mô tả
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      rows="3"
                                      placeholder="Mô tả chi tiết về chương trình giảm giá..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $discount->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Usage Statistics -->
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Thống kê sử dụng</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $discount->used_count }}</div>
                                <div class="text-sm text-gray-600">Lần đã sử dụng</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    @if($discount->usage_limit)
                                        {{ $discount->usage_limit - $discount->used_count }}
                                    @else
                                        ∞
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">Lần có thể sử dụng</div>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Configuration -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Cấu hình giảm giá</h3>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Loại giảm giá <span class="text-red-500">*</span>
                                </label>
                                <select name="type" 
                                        id="type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
                                    <option value="">Chọn loại</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $discount->type) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="discount_category" class="block text-sm font-medium text-gray-700 mb-2">
                                    Danh mục <span class="text-red-500">*</span>
                                </label>
                                <select name="discount_category" 
                                        id="discount_category"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('discount_category') border-red-500 @enderror">
                                    <option value="">Chọn danh mục</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('discount_category', $discount->discount_category) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('discount_category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Value -->
                        <div class="mb-4">
                            <label for="value" class="block text-sm font-medium text-gray-700 mb-2">
                                Giá trị giảm <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="value" 
                                       id="value"
                                       value="{{ old('value', $discount->value) }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('value') border-red-500 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span id="value-unit" class="text-gray-500 text-sm">
                                        @if($discount->type === 'percentage')%@else đ @endif
                                    </span>
                                </div>
                            </div>
                            @error('value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Can Stack -->
                        <div class="flex items-center mb-4">
                            <input type="hidden" name="can_stack" value="0">
                            <input type="checkbox" 
                                   name="can_stack" 
                                   id="can_stack"
                                   value="1"
                                   {{ old('can_stack', $discount->can_stack) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="can_stack" class="ml-2 text-sm font-medium text-gray-700">
                                Có thể chồng với discount khác
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Conditions & Limits -->
                <div class="space-y-6">
                    <!-- Conditions -->
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Điều kiện áp dụng</h3>
                        
                        <!-- Min Order Amount -->
                        <div class="mb-4">
                            <label for="min_order_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Giá trị đơn hàng tối thiểu <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="min_order_amount" 
                                       id="min_order_amount"
                                       value="{{ old('min_order_amount', $discount->min_order_amount) }}"
                                       step="1000"
                                       min="0"
                                       placeholder="0"
                                       class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('min_order_amount') border-red-500 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 text-sm">đ</span>
                                </div>
                            </div>
                            @error('min_order_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Discount Amount -->
                        <div class="mb-4">
                            <label for="max_discount_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Số tiền giảm tối đa
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="max_discount_amount" 
                                       id="max_discount_amount"
                                       value="{{ old('max_discount_amount', $discount->max_discount_amount) }}"
                                       step="1000"
                                       min="0"
                                       placeholder="Không giới hạn"
                                       class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('max_discount_amount') border-red-500 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 text-sm">đ</span>
                                </div>
                            </div>
                            @error('max_discount_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Usage Limit -->
                        <div>
                            <label for="usage_limit" class="block text-sm font-medium text-gray-700 mb-2">
                                Số lần sử dụng tối đa
                            </label>
                            <input type="number" 
                                   name="usage_limit" 
                                   id="usage_limit"
                                   value="{{ old('usage_limit', $discount->usage_limit) }}"
                                   min="1"
                                   placeholder="Không giới hạn"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('usage_limit') border-red-500 @enderror">
                            @error('usage_limit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            @if($discount->usage_limit && $discount->used_count >= $discount->usage_limit)
                                <p class="mt-1 text-sm text-amber-600">
                                    ⚠️ Discount đã đạt giới hạn sử dụng
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Time Period -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Thời gian áp dụng</h3>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ngày bắt đầu <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="start_date" 
                                       id="start_date"
                                       value="{{ old('start_date', $discount->start_date->format('Y-m-d\TH:i')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ngày kết thúc <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="end_date" 
                                       id="end_date"
                                       value="{{ old('end_date', $discount->end_date->format('Y-m-d\TH:i')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Time Status -->
                        <div class="mb-4 p-3 rounded-lg {{ now()->between($discount->start_date, $discount->end_date) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            @if(now()->lt($discount->start_date))
                                ⏳ Discount chưa bắt đầu ({{ $discount->start_date->diffForHumans() }})
                            @elseif(now()->between($discount->start_date, $discount->end_date))
                                ✅ Discount đang trong thời gian hiệu lực
                            @else
                                ❌ Discount đã hết hạn ({{ $discount->end_date->diffForHumans() }})
                            @endif
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $discount->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                                Kích hoạt
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('discounts.show', $discount) }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Hủy
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Cập nhật Discount
                </button>
            </div>
        </form>
    </div>

    <script>
        // Update value unit based on discount type
        document.getElementById('type').addEventListener('change', function() {
            const valueUnit = document.getElementById('value-unit');
            const valueInput = document.getElementById('value');
            
            if (this.value === 'percentage') {
                valueUnit.textContent = '%';
                valueInput.setAttribute('max', '100');
            } else {
                valueUnit.textContent = 'đ';
                valueInput.removeAttribute('max');
            }
        });
    </script>
@endsection
