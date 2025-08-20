# Hướng dẫn Setup E-commerce System

## Cấu trúc Database đã thiết kế

Hệ thống đã được thiết kế với 9 bảng chính:

1. **users** - Quản lý người dùng (admin, staff, customer)
2. **customer_types** - Phân loại khách hàng với ưu đãi khác nhau
3. **customers** - Thông tin khách hàng
4. **products** - Sản phẩm (không có variant)
5. **discounts** - Mã giảm giá/chương trình ưu đãi (có thể chồng nhau)
6. **payment_methods** - Phương thức thanh toán
7. **orders** - Đơn hàng
8. **order_items** - Chi tiết sản phẩm trong đơn hàng
9. **order_discounts** - Áp dụng mã giảm giá cho đơn hàng

## Kiến trúc 3-tier đã implement

### 1. Presentation Layer (Controllers)

-   `CustomerController` - Quản lý khách hàng
-   `OrderController` - Quản lý đơn hàng
-   `ProductController` - Quản lý sản phẩm (chưa hoàn thành)
-   `DiscountController` - Quản lý mã giảm giá (chưa hoàn thành)

### 2. Business Logic Layer (Services)

-   `CustomerService` - Xử lý logic nghiệp vụ khách hàng
-   `OrderService` - Xử lý logic tạo đơn hàng, tính toán giảm giá
-   `ProductService` - Xử lý logic sản phẩm, quản lý tồn kho
-   `DiscountService` - Xử lý logic mã giảm giá

### 3. Data Access Layer (Repositories)

-   Các Repository với Interface pattern
-   BaseRepository chứa các method chung
-   Specific repositories cho từng entity

## Cách setup dự án

### 1. Database Configuration

Cập nhật file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ecommerce
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Chạy migrations

```bash
php artisan migrate
```

### 3. Tạo sample data

Tạo CustomerTypes mẫu:

```sql
INSERT INTO customer_types (name, description, discount_percentage, min_order_amount, is_active, created_at, updated_at) VALUES
('Regular', 'Khách hàng thường', 0.00, 0.00, 1, NOW(), NOW()),
('VIP', 'Khách hàng VIP', 5.00, 1000000.00, 1, NOW(), NOW()),
('Premium', 'Khách hàng Premium', 10.00, 2000000.00, 1, NOW(), NOW());
```

Tạo PaymentMethods mẫu:

```sql
INSERT INTO payment_methods (name, description, is_active, created_at, updated_at) VALUES
('Tiền mặt', 'Thanh toán bằng tiền mặt', 1, NOW(), NOW()),
('Thẻ tín dụng', 'Thanh toán bằng thẻ tín dụng', 1, NOW(), NOW()),
('Chuyển khoản', 'Thanh toán bằng chuyển khoản ngân hàng', 1, NOW(), NOW());
```

## API Endpoints

### Customers

-   `GET /api/v1/customers` - Danh sách khách hàng
-   `POST /api/v1/customers` - Tạo khách hàng mới
-   `GET /api/v1/customers/{id}` - Chi tiết khách hàng
-   `PUT /api/v1/customers/{id}` - Cập nhật khách hàng
-   `GET /api/v1/customers/search?term=...` - Tìm kiếm khách hàng

### Orders

-   `GET /api/v1/orders` - Danh sách đơn hàng
-   `POST /api/v1/orders` - Tạo đơn hàng mới
-   `GET /api/v1/orders/{id}` - Chi tiết đơn hàng
-   `PATCH /api/v1/orders/{id}/status` - Cập nhật trạng thái
-   `PATCH /api/v1/orders/{id}/cancel` - Hủy đơn hàng
-   `GET /api/v1/orders/revenue/report` - Báo cáo doanh thu

## Tính năng đặc biệt

### 1. Hệ thống giảm giá đa tầng

-   Giảm giá theo loại khách hàng (customer_types)
-   Mã giảm giá có thể chồng nhau (can_stack)
-   Giới hạn số lần sử dụng và thời gian

### 2. Quản lý tồn kho tự động

-   Tự động trừ tồn kho khi tạo đơn hàng
-   Hoàn trả tồn kho khi hủy đơn hàng
-   Cập nhật trạng thái sản phẩm khi hết hàng

### 3. Tạo số đơn hàng tự động

-   Format: ORD + YYYYMMDD + số thứ tự (ORD20240820001)

## Sample JSON để test API

### Tạo khách hàng:

```json
{
    "customer_type_id": 1,
    "name": "Nguyễn Văn A",
    "email": "nguyenvana@email.com",
    "phone": "0123456789",
    "address": "123 Đường ABC, Quận 1, TP.HCM"
}
```

### Tạo đơn hàng:

```json
{
    "customer_id": 1,
    "payment_method_id": 1,
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ],
    "discount_codes": ["SAVE10", "WELCOME20"],
    "notes": "Ghi chú đơn hàng",
    "shipping_address": "Địa chỉ giao hàng"
}
```

## Các extension có thể thêm

1. Authentication & Authorization middleware
2. Product categories
3. Customer order history
4. Inventory tracking
5. Email notifications
6. Payment gateway integration
7. Admin dashboard
8. Reporting system
