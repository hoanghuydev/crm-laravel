<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng #{{ $orderData['order_number'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .order-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .order-info h2 {
            margin-top: 0;
            color: #007bff;
            font-size: 18px;
        }
        .customer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #495057;
            font-size: 16px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .items-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .items-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .price {
            font-weight: bold;
            color: #28a745;
        }
        .discount {
            color: #dc3545;
            font-weight: bold;
        }
        .total-section {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
        }
        .total-row.final {
            border-top: 2px solid #007bff;
            padding-top: 10px;
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .discounts-section {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .discounts-section h3 {
            color: #155724;
            margin-top: 0;
        }
        .discount-item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 5px 0;
        }
        @media (max-width: 600px) {
            .customer-info {
                grid-template-columns: 1fr;
            }
            .total-row {
                flex-direction: column;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Cảm ơn bạn đã đặt hàng!</h1>
            <p>Đơn hàng #{{ $orderData['order_number'] }} của bạn đã được xác nhận</p>
        </div>

        <div class="order-info">
            <h2>📋 Thông tin đơn hàng</h2>
            <p><strong>Số đơn hàng:</strong> #{{ $orderData['order_number'] }}</p>
            <p><strong>Ngày đặt:</strong> {{ \Carbon\Carbon::parse($orderDetails['order_date'])->format('d/m/Y H:i') }}</p>
            <p><strong>Trạng thái:</strong> 
                <span style="background-color: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                    {{ ucfirst($orderDetails['status']) }}
                </span>
            </p>
        </div>

        <div class="customer-info">
            <div class="info-section">
                <h3>👤 Thông tin khách hàng</h3>
                <p><strong>Tên:</strong> {{ $customer['name'] }}</p>
                <p><strong>Email:</strong> {{ $customer['email'] }}</p>
                @if($customer['phone'])
                    <p><strong>Số điện thoại:</strong> {{ $customer['phone'] }}</p>
                @endif
                @if($customer['type'])
                    <p><strong>Loại khách hàng:</strong> {{ $customer['type'] }}</p>
                @endif
            </div>

            <div class="info-section">
                <h3>💳 Phương thức thanh toán</h3>
                <p><strong>Tên:</strong> {{ $paymentMethod['name'] }}</p>
                <p><strong>Loại:</strong> {{ ucfirst($paymentMethod['type']) }}</p>
            </div>
        </div>

        @if($orderDetails['shipping_address'])
            <div class="info-section" style="margin: 20px 0;">
                <h3>🚚 Địa chỉ giao hàng</h3>
                <p>{{ $orderDetails['shipping_address'] }}</p>
            </div>
        @endif

        <h2 style="color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;">🛍️ Chi tiết sản phẩm</h2>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="text-align: center;">Số lượng</th>
                    <th style="text-align: right;">Đơn giá</th>
                    <th style="text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item['product_name'] }}</strong>
                            <br>
                            <small style="color: #6c757d;">ID: {{ $item['product_id'] }}</small>
                        </td>
                        <td style="text-align: center;">{{ number_format($item['quantity']) }}</td>
                        <td style="text-align: right;" class="price">{{ number_format($item['unit_price'], 0, ',', '.') }}đ</td>
                        <td style="text-align: right;" class="price">{{ number_format($item['total_price'], 0, ',', '.') }}đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($discounts) > 0)
            <div class="discounts-section">
                <h3>🎁 Mã giảm giá đã áp dụng</h3>
                @foreach($discounts as $discount)
                    <div class="discount-item">
                        <span>
                            <strong>{{ $discount['discount_name'] }}</strong>
                            <br>
                            <small>Mã: {{ $discount['discount_code'] }}</small>
                        </span>
                        <span class="discount">-{{ number_format($discount['discount_amount'], 0, ',', '.') }}đ</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="total-section">
            <div class="total-row">
                <span>Tạm tính:</span>
                <span class="price">{{ number_format($orderDetails['subtotal'], 0, ',', '.') }}đ</span>
            </div>
            
            @if($orderDetails['customer_discount_amount'] > 0)
                <div class="total-row">
                    <span>Giảm giá khách hàng:</span>
                    <span class="discount">-{{ number_format($orderDetails['customer_discount_amount'], 0, ',', '.') }}đ</span>
                </div>
            @endif
            
            @if($orderDetails['discount_amount'] > 0)
                <div class="total-row">
                    <span>Giảm giá mã khuyến mãi:</span>
                    <span class="discount">-{{ number_format($orderDetails['discount_amount'], 0, ',', '.') }}đ</span>
                </div>
            @endif
            
            <div class="total-row final">
                <span>Tổng cộng:</span>
                <span>{{ number_format($orderDetails['total'], 0, ',', '.') }}đ</span>
            </div>
        </div>

        @if($orderDetails['notes'])
            <div class="info-section" style="margin: 20px 0;">
                <h3>📝 Ghi chú</h3>
                <p>{{ $orderDetails['notes'] }}</p>
            </div>
        @endif

        <div class="footer">
            <p><strong>Cảm ơn bạn đã tin tưởng và mua hàng tại cửa hàng của chúng tôi!</strong></p>
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email: {{ config('mail.from.address') }}</p>
            <p style="margin-top: 20px;">
                <small>Email này được gửi tự động, vui lòng không trả lời trực tiếp email này.</small>
            </p>
        </div>
    </div>
</body>
</html>
