@extends('layouts.app')

@section('title', 'Chi tiết Discount')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Chi tiết Discount</h1>
                        <p class="text-sm text-gray-600 mt-1">Thông tin chi tiết về discount "{{ $discount->code }}"</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('discounts.edit', $discount) }}" 
                           class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Chỉnh sửa
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

            <!-- Status Banner -->
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Status -->
                    <div class="text-center">
                        @if($discount->is_active)
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Đang hoạt động
                            </div>
                        @else
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Không hoạt động
                            </div>
                        @endif
                    </div>

                    <!-- Time Status -->
                    <div class="text-center">
                        @if(now()->lt($discount->start_date))
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Chưa bắt đầu
                            </div>
                        @elseif(now()->between($discount->start_date, $discount->end_date))
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Đang hiệu lực
                            </div>
                        @else
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Đã hết hạn
                            </div>
                        @endif
                    </div>

                    <!-- Usage Status -->
                    <div class="text-center">
                        @if($discount->usage_limit)
                            @if($discount->used_count >= $discount->usage_limit)
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    Đã hết lượt
                                </div>
                            @else
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Còn lượt sử dụng
                                </div>
                            @endif
                        @else
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Không giới hạn
                            </div>
                        @endif
                    </div>

                    <!-- Stacking -->
                    <div class="text-center">
                        @if($discount->can_stack)
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                Có thể chồng
                            </div>
                        @else
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Không chồng
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin cơ bản</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Mã giảm giá:</span>
                        <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $discount->code }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Tên chương trình:</span>
                        <span class="text-sm text-gray-900">{{ $discount->name }}</span>
                    </div>
                    
                    @if($discount->description)
                        <div class="pt-2 border-t border-gray-200">
                            <span class="text-sm font-medium text-gray-500">Mô tả:</span>
                            <p class="text-sm text-gray-700 mt-1">{{ $discount->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Discount Details -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Chi tiết giảm giá</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Loại giảm giá:</span>
                        <span class="text-sm text-gray-900">
                            @if($discount->type === 'percentage')
                                Phần trăm (%)
                            @else
                                Số tiền cố định (đ)
                            @endif
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Danh mục:</span>
                        @php
                            $categoryLabels = [
                                'product' => ['label' => 'Sản phẩm', 'color' => 'blue'],
                                'payment' => ['label' => 'Thanh toán', 'color' => 'green'],
                                'customer' => ['label' => 'Khách hàng', 'color' => 'purple'],
                                'seasonal' => ['label' => 'Theo mùa', 'color' => 'yellow'],
                                'promotion' => ['label' => 'Khuyến mại', 'color' => 'red']
                            ];
                            $categoryInfo = $categoryLabels[$discount->discount_category] ?? ['label' => $discount->discount_category, 'color' => 'gray'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $categoryInfo['color'] }}-100 text-{{ $categoryInfo['color'] }}-800">
                            {{ $categoryInfo['label'] }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Giá trị giảm:</span>
                        <span class="text-lg font-bold text-green-600">
                            @if($discount->type === 'percentage')
                                {{ number_format($discount->value, 0) }}%
                            @else
                                {{ number_format($discount->value, 0) }}đ
                            @endif
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Giá trị đơn hàng tối thiểu:</span>
                        <span class="text-sm text-gray-900">{{ number_format($discount->min_order_amount, 0) }}đ</span>
                    </div>
                    
                    @if($discount->max_discount_amount)
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Số tiền giảm tối đa:</span>
                            <span class="text-sm text-gray-900">{{ number_format($discount->max_discount_amount, 0) }}đ</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Thống kê sử dụng</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Đã sử dụng:</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $discount->used_count }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Giới hạn sử dụng:</span>
                        <span class="text-2xl font-bold text-gray-900">
                            @if($discount->usage_limit)
                                {{ $discount->usage_limit }}
                            @else
                                ∞
                            @endif
                        </span>
                    </div>
                    
                    @if($discount->usage_limit)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-500">Tỷ lệ sử dụng:</span>
                                <span class="text-gray-900">{{ $usagePercentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($usagePercentage, 100) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Time Period -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Thời gian áp dụng</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Ngày bắt đầu:</span>
                        <span class="text-sm text-gray-900">{{ $discount->start_date->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Ngày kết thúc:</span>
                        <span class="text-sm text-gray-900">{{ $discount->end_date->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Thời gian còn lại:</span>
                        <span class="text-sm text-gray-900">
                            @if(now()->lt($discount->start_date))
                                Bắt đầu {{ $discount->start_date->diffForHumans() }}
                            @elseif(now()->between($discount->start_date, $discount->end_date))
                                Kết thúc {{ $discount->end_date->diffForHumans() }}
                            @else
                                Đã kết thúc {{ $discount->end_date->diffForHumans() }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stacking Information -->
        @if($discount->can_stack)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin chồng discount</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Danh mục hiện tại:</h3>
                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-{{ $categoryInfo['color'] }}-100 text-{{ $categoryInfo['color'] }}-800">
                            {{ $categoryInfo['label'] }}
                        </span>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Có thể chồng với:</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($compatibleCategories as $category)
                                @php
                                    $compatibleInfo = $categoryLabels[$category] ?? ['label' => $category, 'color' => 'gray'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $compatibleInfo['color'] }}-100 text-{{ $compatibleInfo['color'] }}-800">
                                    {{ $compatibleInfo['label'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Thao tác nhanh</h2>
            
            <div class="flex space-x-3">
                <form action="{{ route('discounts.toggle-status', $discount) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 {{ $discount->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2">
                        @if($discount->is_active)
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Vô hiệu hóa
                        @else
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Kích hoạt
                        @endif
                    </button>
                </form>
                
                
                <form action="{{ route('discounts.destroy', $discount) }}" method="POST" class="inline" 
                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa discount này? Hành động này không thể hoàn tác.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
