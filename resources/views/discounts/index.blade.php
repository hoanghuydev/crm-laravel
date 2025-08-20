@extends('layouts.app')

@section('title', 'Quản lý Discount')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Quản lý Discount</h1>
                    <p class="text-sm text-gray-600 mt-1">Quản lý các chương trình giảm giá và thuật toán chồng discount</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('discounts.test-stacking') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Test Stacking
                    </a>
                    <a href="{{ route('discounts.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Thêm Discount
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('discounts.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Tìm kiếm theo mã hoặc tên..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                </select>

                <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tất cả danh mục</option>
                    <option value="product" {{ request('category') == 'product' ? 'selected' : '' }}>Sản phẩm</option>
                    <option value="payment" {{ request('category') == 'payment' ? 'selected' : '' }}>Thanh toán</option>
                    <option value="customer" {{ request('category') == 'customer' ? 'selected' : '' }}>Khách hàng</option>
                    <option value="seasonal" {{ request('category') == 'seasonal' ? 'selected' : '' }}>Theo mùa</option>
                    <option value="promotion" {{ request('category') == 'promotion' ? 'selected' : '' }}>Khuyến mại</option>
                </select>

                <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tất cả loại</option>
                    <option value="percentage" {{ request('type') == 'percentage' ? 'selected' : '' }}>Phần trăm</option>
                    <option value="fixed_amount" {{ request('type') == 'fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                </select>

                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Lọc
                </button>

                @if(request()->hasAny(['search', 'status', 'category', 'type']))
                    <a href="{{ route('discounts.index') }}" 
                       class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Xóa bộ lọc
                    </a>
                @endif
            </form>
        </div>

        <!-- Discounts Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã & Tên</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Danh mục</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại & Giá trị</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sử dụng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chồng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($discounts as $discount)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $discount->code }}</div>
                                    <div class="text-sm text-gray-500">{{ $discount->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
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
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    @if($discount->type === 'percentage')
                                        {{ number_format($discount->value, 0) }}%
                                    @else
                                        {{ number_format($discount->value, 0) }}đ
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    @if($discount->type === 'percentage')
                                        Phần trăm
                                    @else
                                        Cố định
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $discount->start_date->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">đến {{ $discount->end_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $discount->used_count }}</div>
                                @if($discount->usage_limit)
                                    <div class="text-xs text-gray-500">/ {{ $discount->usage_limit }}</div>
                                @else
                                    <div class="text-xs text-gray-500">/ Không giới hạn</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($discount->can_stack)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Có thể chồng
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Không chồng
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($discount->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Không hoạt động
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('discounts.show', $discount) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Xem chi tiết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    
                                    <a href="{{ route('discounts.edit', $discount) }}" 
                                       class="text-yellow-600 hover:text-yellow-900" title="Chỉnh sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('discounts.toggle-status', $discount) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="text-gray-600 hover:text-gray-900" 
                                                title="{{ $discount->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                            @if($discount->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('discounts.destroy', $discount) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa discount này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900" title="Xóa">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2m16-7H4"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Không có discount nào</h3>
                                    <p class="mt-1 text-sm text-gray-500">Bắt đầu bằng cách tạo discount đầu tiên.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('discounts.create') }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
                                            </svg>
                                            Tạo Discount
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($discounts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $discounts->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
