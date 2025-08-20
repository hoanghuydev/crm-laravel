# Customer Classification System

## Tổng Quan

Hệ thống phân loại khách hàng sử dụng các Design Patterns để tự động phân loại khách hàng dựa trên điểm số (scoring system). Hệ thống áp dụng các nguyên lý SOLID và OOP để đảm bảo tính mở rộng và bảo trì.

## Kiến Trúc Hệ Thống

### Design Patterns Sử Dụng

1. **Strategy Pattern** - Cho việc tính điểm từng metric
2. **Factory Pattern** - Tạo pricing strategies
3. **Observer Pattern** - Lắng nghe sự kiện tạo đơn hàng mới
4. **Repository Pattern** - Truy cập dữ liệu

### Scoring System

Hệ thống tính điểm dựa trên 4 tiêu chí chính với trọng số khác nhau:

| Tiêu Chí              | Trọng Số Mặc Định | Mô Tả                             |
| --------------------- | ----------------- | --------------------------------- |
| Tổng giá trị đơn hàng | 35%               | Tổng tiền khách hàng đã chi tiêu  |
| Số lượng đơn hàng     | 25%               | Tổng số đơn hàng đã đặt           |
| Tần suất đặt hàng     | 25%               | Thời gian trung bình giữa các đơn |
| Vị trí địa lý         | 15%               | Khách ở HCM được ưu tiên          |

### Chuẩn Hóa Điểm Số

Tất cả điểm được chuẩn hóa về thang điểm 0-1:

-   **Tổng giá trị**: 10 triệu VND = 1.0 điểm
-   **Số đơn hàng**: 20 đơn = 1.0 điểm
-   **Tần suất**: 60 ngày trung bình = 0 điểm, ít hơn = điểm cao hơn
-   **Vị trí**: HCM = 1.0, khác = 0.3

## Cấu Trúc Dữ Liệu

### Customer Types

```sql
-- Bảng customer_types
minimum_score DECIMAL(5,3)    -- Điểm tối thiểu để đạt loại này
priority INT                  -- Độ ưu tiên (cao hơn = ưu tiên hơn)
scoring_weights JSON          -- Trọng số tùy chỉnh (tuỳ chọn)
```

### Customers

```sql
-- Bảng customers - Các trường scoring
current_score DECIMAL(5,3)           -- Điểm tổng hiện tại
total_value_score DECIMAL(5,3)       -- Điểm từ tổng chi tiêu
order_frequency_score DECIMAL(5,3)   -- Điểm từ tần suất
order_count_score DECIMAL(5,3)       -- Điểm từ số đơn hàng
location_score DECIMAL(5,3)          -- Điểm từ vị trí
last_score_calculated_at TIMESTAMP   -- Lần tính điểm cuối
```

## Luồng Hoạt Động

### 1. Tính Điểm Tự Động

```
Đơn hàng mới → OrderCreated Event → RecalculateCustomerScore Listener →
CustomerScoringService → Tính điểm → Phân loại lại → Cập nhật DB
```

### 2. Phân Loại Khách Hàng

```php
// Thuật toán phân loại
function determineCustomerType(Customer $customer) {
    // Lấy tất cả loại khách theo độ ưu tiên (cao → thấp)
    $types = CustomerType::active()->byPriority()->get();

    // Tìm loại cao nhất mà khách đủ điều kiện
    foreach ($types as $type) {
        if ($customer->current_score >= $type->minimum_score) {
            return $type;
        }
    }

    // Fallback về loại thấp nhất
    return $types->sortBy('priority')->first();
}
```

### 3. Tính Giảm Giá

```php
// Sử dụng Strategy Pattern
$pricingStrategy = PricingStrategyFactory::create($customer);
$discount = $pricingStrategy->calculateDiscount($amount, $customer);
```

## Cách Sử Dụng

### 1. Setup Database

```bash
# Chạy migration
php artisan migrate

# Seed customer types với điểm phân loại
php artisan db:seed --class=CustomerTypeScoringSeeder

# Tạo dữ liệu test (tuỳ chọn)
php artisan db:seed --class=TestCustomersSeeder
```

### 2. Tính Điểm Khách Hàng

```php
use App\Services\CustomerScoringService;

$scoringService = app(CustomerScoringService::class);

// Tính điểm và phân loại lại một khách hàng
$wasReclassified = $scoringService->reclassifyCustomer($customer);

// Tính điểm tất cả khách hàng
$results = $scoringService->reclassifyAllCustomers();

// Xem chi tiết điểm số
$breakdown = $scoringService->getCustomerScoreBreakdown($customer);
```

### 3. Tạo Đơn Hàng (Tự Động Trigger Scoring)

```php
use App\Services\OrderService;

$orderService = app(OrderService::class);

// Tạo đơn hàng → Tự động fire OrderCreated event → Tính lại điểm
$order = $orderService->createOrder($orderData, $items, $discountCodes);
```

### 4. Quản Lý Customer Types

```php
// Thêm loại khách hàng mới
CustomerType::create([
    'name' => 'Khách Hàng Bạch Kim',
    'discount_percentage' => 20,
    'minimum_score' => 0.95,
    'priority' => 7,
    'scoring_weights' => [
        'total_value_weight' => 0.5,
        'order_count_weight' => 0.25,
        'order_frequency_weight' => 0.15,
        'location_weight' => 0.1,
    ]
]);
```

## Mở Rộng Hệ Thống

### 1. Thêm Metric Scoring Mới

```php
// Tạo strategy mới
class NewMetricScoringStrategy implements ScoringStrategyInterface
{
    public function calculateScore(Customer $customer, array $data = []): float
    {
        // Logic tính điểm mới
        return $normalizedScore;
    }

    public function getWeight(): float
    {
        return 0.10; // 10% weight
    }
}

// Thêm vào CustomerScoringService
protected array $strategies = [
    // ... existing strategies
    new NewMetricScoringStrategy(),
];
```

### 2. Tùy Chỉnh Pricing Strategy

```php
class CustomPricingStrategy implements PricingStrategyInterface
{
    public function calculateDiscount(float $amount, Customer $customer): float
    {
        // Logic giảm giá tùy chỉnh
        return $discountAmount;
    }
}

// Cập nhật Factory
class PricingStrategyFactory
{
    public static function create(Customer $customer): PricingStrategyInterface
    {
        if ($customer->customerType->code === 'CUSTOM') {
            return new CustomPricingStrategy();
        }

        return new DatabasePricingStrategy();
    }
}
```

### 3. Thêm Event Listeners Khác

```php
// Event listener cho các sự kiện khác
class CustomerUpdatedListener
{
    public function handle(CustomerUpdated $event): void
    {
        if ($event->customer->wasChanged(['address'])) {
            // Tính lại điểm nếu địa chỉ thay đổi
            $this->scoringService->reclassifyCustomer($event->customer);
        }
    }
}
```

## Điểm Phân Loại Test

Hệ thống được cấu hình với điểm phân loại thấp để dễ test:

| Loại Khách Hàng | Điểm Tối Thiểu | Giảm Giá | Đơn Tối Thiểu |
| --------------- | -------------- | -------- | ------------- |
| Thường          | 0.0            | 0%       | 0đ            |
| Đồng            | 0.15           | 3%       | 300k          |
| Bạc             | 0.30           | 5%       | 200k          |
| Vàng            | 0.50           | 8%       | 100k          |
| VIP             | 0.70           | 12%      | 50k           |
| Premium         | 0.85           | 15%      | 0đ            |

## Testing

### 1. Test Scoring Logic

```php
// Test một khách hàng cụ thể
$customer = Customer::find(1);
$breakdown = app(CustomerScoringService::class)
    ->getCustomerScoreBreakdown($customer);

dd($breakdown);
```

### 2. Test Reclassification

```php
// Tạo đơn hàng test và xem khách hàng có được phân loại lại không
$order = Order::factory()->create(['customer_id' => 1]);
Event::dispatch(new OrderCreated($order));

$customer = Customer::find(1);
echo "New type: " . $customer->customerType->name;
```

### 3. Debug Scoring

```php
// Xem chi tiết tính điểm
$customer = Customer::with('customerType')->find(1);

echo "Customer: " . $customer->name . "\n";
echo "Current Score: " . $customer->current_score . "\n";
echo "Customer Type: " . $customer->customerType->name . "\n";
echo "Total Spent: " . $customer->getTotalSpent() . "\n";
echo "Total Orders: " . $customer->getTotalOrderCount() . "\n";
echo "Avg Days Between Orders: " . $customer->getAverageDaysBetweenOrders() . "\n";
echo "Is From HCM: " . ($customer->isFromHCM() ? 'Yes' : 'No') . "\n";

print_r($customer->getScoreBreakdown());
```

## Lưu Ý Quan Trọng

1. **Performance**: Listener chạy queue để không ảnh hưởng đến tốc độ tạo đơn hàng
2. **Consistency**: Điểm được tính dựa trên đơn hàng hoàn thành, không phải đơn pending
3. **Extensibility**: Dễ dàng thêm metric mới thông qua Strategy pattern
4. **Data Integrity**: Không lưu trữ total_spent, total_orders để tránh inconsistency
5. **Flexibility**: Trọng số có thể tùy chỉnh cho từng loại khách hàng

## API Endpoints (Tham Khảo)

```php
// routes/api.php
Route::prefix('customers')->group(function () {
    Route::post('/{id}/recalculate-score', [CustomerController::class, 'recalculateScore']);
    Route::get('/{id}/score-breakdown', [CustomerController::class, 'getScoreBreakdown']);
    Route::post('/batch-recalculate', [CustomerController::class, 'batchRecalculate']);
});
```

Hệ thống này cung cấp foundation mạnh mẽ và linh hoạt cho việc phân loại khách hàng tự động, có thể dễ dàng mở rộng khi yêu cầu business thay đổi.
