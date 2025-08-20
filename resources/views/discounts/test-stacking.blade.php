@extends('layouts.app')

@section('title', 'Test Discount Stacking')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Test Discount Stacking Algorithm</h1>
                        <p class="text-sm text-gray-600 mt-1">Kiểm tra thuật toán chồng discount với các kịch bản khác nhau</p>
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

            <!-- Algorithm Info -->
            <div class="px-6 py-4 bg-blue-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-blue-900 mb-3">Cách thức hoạt động của thuật toán:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-blue-800">
                    <div>
                        <h4 class="font-semibold mb-2">Quy tắc chồng discount:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Sản phẩm:</strong> chồng với Thanh toán + Khách hàng</li>
                            <li><strong>Thanh toán:</strong> chồng với Sản phẩm + Theo mùa</li>
                            <li><strong>Khách hàng:</strong> chồng với Sản phẩm + Khuyến mại</li>
                            <li><strong>Theo mùa:</strong> chồng với Thanh toán + Khuyến mại</li>
                            <li><strong>Khuyến mại:</strong> chồng với Khách hàng + Theo mùa</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Thuật toán Greedy:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Ưu tiên discount có giá trị cao nhất trong mỗi danh mục</li>
                            <li>Áp dụng theo thứ tự giảm dần của giá trị discount</li>
                            <li>Kiểm tra khả năng chồng giữa các danh mục</li>
                            <li>Đảm bảo tổng giảm giá ≤ giá trị đơn hàng</li>
                            <li>Ghi lại các discount bị loại bỏ và lý do</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Kiểm tra Discount Stacking</h2>
            
            <form method="GET" action="{{ route('discounts.test-stacking') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="discount_codes" class="block text-sm font-medium text-gray-700 mb-2">
                            Mã discount (phân cách bằng dấu phẩy)
                        </label>
                        <input type="text" 
                               name="discount_codes" 
                               id="discount_codes"
                               value="{{ implode(', ', $discountCodes) }}"
                               placeholder="VD: SAVE10, PAYMENT5, CUSTOMER20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Nhập các mã discount cần kiểm tra, cách nhau bằng dấu phẩy</p>
                    </div>
                    
                    <div>
                        <label for="order_amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Giá trị đơn hàng (VNĐ)
                        </label>
                        <input type="number" 
                               name="order_amount" 
                               id="order_amount"
                               value="{{ $orderAmount }}"
                               placeholder="1000000"
                               step="1000"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Nhập giá trị đơn hàng để tính toán discount</p>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Kiểm tra Stacking
                    </button>
                    
                    <a href="{{ route('discounts.test-stacking') }}" 
                       class="px-6 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Xóa kết quả
                    </a>
                </div>
            </form>
        </div>

        @if($stackingResult)
            <!-- Results -->
            <div class="space-y-6">
                <!-- Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kết quả tính toán</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($orderAmount, 0) }}đ</div>
                            <div class="text-sm text-gray-600">Giá trị đơn hàng</div>
                        </div>
                        
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ number_format($stackingResult['total_discount'], 0) }}đ</div>
                            <div class="text-sm text-gray-600">Tổng giảm giá</div>
                        </div>
                        
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ number_format($orderAmount - $stackingResult['total_discount'], 0) }}đ</div>
                            <div class="text-sm text-gray-600">Số tiền phải trả</div>
                        </div>
                        
                        <div class="text-center p-4 bg-yellow-50 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ count($stackingResult['applied_discounts']) }}</div>
                            <div class="text-sm text-gray-600">Discount được áp dụng</div>
                        </div>
                    </div>
                </div>

                <!-- Applied Discounts -->
                @if(!empty($stackingResult['applied_discounts']))
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-green-900 mb-4">✅ Discount được áp dụng</h2>
                        
                        <div class="space-y-4">
                            @foreach($stackingResult['applied_discounts'] as $applied)
                                <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-green-900">{{ $applied['discount']->code }}</h3>
                                            <p class="text-sm text-green-700">{{ $applied['discount']->name }}</p>
                                            <div class="flex items-center mt-2 space-x-4">
                                                @php
                                                    $categoryLabels = [
                                                        'product' => ['label' => 'Sản phẩm', 'color' => 'blue'],
                                                        'payment' => ['label' => 'Thanh toán', 'color' => 'green'],
                                                        'customer' => ['label' => 'Khách hàng', 'color' => 'purple'],
                                                        'seasonal' => ['label' => 'Theo mùa', 'color' => 'yellow'],
                                                        'promotion' => ['label' => 'Khuyến mại', 'color' => 'red']
                                                    ];
                                                    $categoryInfo = $categoryLabels[$applied['category']] ?? ['label' => $applied['category'], 'color' => 'gray'];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $categoryInfo['color'] }}-100 text-{{ $categoryInfo['color'] }}-800">
                                                    {{ $categoryInfo['label'] }}
                                                </span>
                                                
                                                @if($applied['discount']->type === 'percentage')
                                                    <span class="text-xs text-green-600">{{ $applied['discount']->value }}%</span>
                                                @else
                                                    <span class="text-xs text-green-600">{{ number_format($applied['discount']->value, 0) }}đ</span>
                                                @endif
                                                
                                                @if(isset($applied['stacked_with']))
                                                    <span class="text-xs text-green-600 bg-green-200 px-2 py-1 rounded">
                                                        🔗 Chồng với {{ $categoryLabels[$applied['stacked_with']]['label'] ?? $applied['stacked_with'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-green-600">-{{ number_format($applied['amount'], 0) }}đ</div>
                                            <div class="text-xs text-green-500">Giảm được</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Conflicts -->
                @if(!empty($stackingResult['stacking_conflicts']))
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-red-900 mb-4">❌ Discount bị loại bỏ</h2>
                        
                        <div class="space-y-4">
                            @foreach($stackingResult['stacking_conflicts'] as $conflict)
                                <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-red-900">{{ $conflict['discount']->code }}</h3>
                                            <p class="text-sm text-red-700">{{ $conflict['discount']->name }}</p>
                                            <div class="flex items-center mt-2 space-x-4">
                                                @php
                                                    $categoryInfo = $categoryLabels[$conflict['discount']->discount_category] ?? ['label' => $conflict['discount']->discount_category, 'color' => 'gray'];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $categoryInfo['color'] }}-100 text-{{ $categoryInfo['color'] }}-800">
                                                    {{ $categoryInfo['label'] }}
                                                </span>
                                                
                                                @if($conflict['discount']->type === 'percentage')
                                                    <span class="text-xs text-red-600">{{ $conflict['discount']->value }}%</span>
                                                @else
                                                    <span class="text-xs text-red-600">{{ number_format($conflict['discount']->value, 0) }}đ</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-red-600">
                                                -{{ number_format($conflict['discount']->calculateDiscountAmount($orderAmount), 0) }}đ
                                            </div>
                                            <div class="text-xs text-red-500">Có thể giảm</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 p-3 bg-red-100 rounded-lg">
                                        <p class="text-sm text-red-800">
                                            <strong>Lý do loại bỏ:</strong> {{ $conflict['reason'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Errors -->
                @if(!empty($stackingResult['errors']))
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-red-900 mb-4">⚠️ Lỗi xác thực</h2>
                        
                        <div class="space-y-2">
                            @foreach($stackingResult['errors'] as $error)
                                <div class="p-3 bg-red-100 border border-red-200 rounded-lg">
                                    <p class="text-sm text-red-800">{{ $error }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Algorithm Explanation -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">🔍 Giải thích thuật toán</h2>
                    
                    <div class="space-y-4 text-sm text-gray-700">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <h4 class="font-semibold text-blue-900 mb-2">Bước 1: Phân nhóm theo danh mục</h4>
                            <p>Thuật toán nhóm các discount theo danh mục và sắp xếp theo giá trị giảm giá từ cao đến thấp trong mỗi nhóm.</p>
                        </div>
                        
                        <div class="p-4 bg-green-50 rounded-lg">
                            <h4 class="font-semibold text-green-900 mb-2">Bước 2: Áp dụng Greedy Algorithm</h4>
                            <p>Chọn discount có giá trị cao nhất từ mỗi danh mục và kiểm tra khả năng chồng với các danh mục khác theo quy tắc được định nghĩa.</p>
                        </div>
                        
                        <div class="p-4 bg-yellow-50 rounded-lg">
                            <h4 class="font-semibold text-yellow-900 mb-2">Bước 3: Tính toán tổng giảm giá</h4>
                            <p>Đảm bảo tổng số tiền giảm không vượt quá giá trị đơn hàng và áp dụng các giới hạn khác như max_discount_amount.</p>
                        </div>
                        
                        <div class="p-4 bg-purple-50 rounded-lg">
                            <h4 class="font-semibold text-purple-900 mb-2">Bước 4: Ghi nhận conflicts</h4>
                            <p>Các discount không thể áp dụng sẽ được ghi lại cùng với lý do cụ thể để người dùng hiểu tại sao chúng bị loại bỏ.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Add some example discount codes when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const discountCodesInput = document.getElementById('discount_codes');
            const orderAmountInput = document.getElementById('order_amount');
            
            // Set default order amount if empty
            if (!orderAmountInput.value) {
                orderAmountInput.value = '1000000';
            }
            
            // Add click handler for quick examples
            const addExampleButton = document.createElement('button');
            addExampleButton.type = 'button';
            addExampleButton.className = 'mt-2 text-xs text-blue-600 hover:text-blue-800';
            addExampleButton.textContent = 'Sử dụng ví dụ mẫu';
            addExampleButton.onclick = function() {
                discountCodesInput.value = 'PRODUCT10, PAYMENT5, CUSTOMER15';
                orderAmountInput.value = '2000000';
            };
            
            discountCodesInput.parentNode.appendChild(addExampleButton);
        });
    </script>
@endsection
