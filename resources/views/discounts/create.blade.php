@extends('layouts.app')

@section('title', 'Tạo Discount Mới')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Tạo Discount Mới</h1>
                    <p class="text-sm text-gray-600 mt-1">Tạo chương trình giảm giá với thuật toán chồng discount</p>
                </div>
                <a href="{{ route('discounts.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Quay lại
                </a>
            </div>
        </div>

        <form action="{{ route('discounts.store') }}" method="POST" class="p-6">
            @csrf
            
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
                                   value="{{ old('code') }}"
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
                                   value="{{ old('name') }}"
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
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
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
                                        <option value="{{ $key }}" {{ old('discount_category') == $key ? 'selected' : '' }}>
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
                                       value="{{ old('value') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('value') border-red-500 @enderror">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span id="value-unit" class="text-gray-500 text-sm">%</span>
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
                                   {{ old('can_stack') ? 'checked' : '' }}
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
                                       value="{{ old('min_order_amount', 0) }}"
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
                                       value="{{ old('max_discount_amount') }}"
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
                                   value="{{ old('usage_limit') }}"
                                   min="1"
                                   placeholder="Không giới hạn"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('usage_limit') border-red-500 @enderror">
                            @error('usage_limit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                                       value="{{ old('start_date') }}"
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
                                       value="{{ old('end_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                                Kích hoạt ngay
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stacking Info -->
            <div class="mt-6 p-4 bg-blue-100 rounded-lg">
                <h4 class="font-medium text-blue-900 mb-2">Thông tin về thuật toán chồng discount</h4>
                <div class="text-sm text-blue-800">
                    <p class="mb-2"><strong>Các danh mục có thể chồng nhau:</strong></p>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        <li><strong>Sản phẩm:</strong> có thể chồng với Thanh toán và Khách hàng</li>
                        <li><strong>Thanh toán:</strong> có thể chồng với Sản phẩm và Theo mùa</li>
                        <li><strong>Khách hàng:</strong> có thể chồng với Sản phẩm và Khuyến mại</li>
                        <li><strong>Theo mùa:</strong> có thể chồng với Thanh toán và Khuyến mại</li>
                        <li><strong>Khuyến mại:</strong> có thể chồng với Khách hàng và Theo mùa</li>
                    </ul>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('discounts.index') }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Hủy
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Tạo Discount
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

        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            
            if (!startDate.value) {
                const now = new Date();
                startDate.value = now.toISOString().slice(0, 16);
            }
            
            if (!endDate.value) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 7);
                endDate.value = tomorrow.toISOString().slice(0, 16);
            }
        });
    </script>
@endsection
