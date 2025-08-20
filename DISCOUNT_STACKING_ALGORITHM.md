# Discount Stacking Algorithm Documentation

## Tổng quan

Hệ thống giảm giá này implement một thuật toán stacking cho phép chồng các discount thuộc các danh mục khác nhau. Thuật toán sử dụng **Greedy Algorithm** để tối ưu hóa tổng số tiền giảm giá cho khách hàng.

## Cấu trúc Discount Categories

### Các danh mục discount và khả năng stacking:

```
┌─────────────┬─────────────────────────────────────┐
│   Category  │        Có thể chồng với             │
├─────────────┼─────────────────────────────────────┤
│  Product    │  Payment, Customer                  │
│  Payment    │  Product, Seasonal                  │
│  Customer   │  Product, Promotion                 │
│  Seasonal   │  Payment, Promotion                 │
│  Promotion  │  Customer, Seasonal                 │
└─────────────┴─────────────────────────────────────┘
```

### Ví dụ về các kịch bản stacking:

-   ✅ **Có thể chồng**: Product discount + Payment discount
-   ✅ **Có thể chồng**: Customer discount + Product discount
-   ❌ **Không thể chồng**: Product discount + Product discount khác
-   ❌ **Không thể chồng**: Payment discount + Customer discount

## Thuật toán Greedy Stacking

### Bước 1: Validation & Grouping

```php
// Validate từng discount code
foreach ($codes as $code) {
    $validation = $this->validateDiscountForOrder($code, $orderAmount);
    if ($validation['valid']) {
        $validDiscounts[] = $validation['discount'];
    }
}

// Nhóm discount theo category
$discountsByCategory = [];
foreach ($discounts as $discount) {
    $discountsByCategory[$discount->discount_category][] = $discount;
}
```

### Bước 2: Prioritization

```php
// Sắp xếp discount trong mỗi category theo giá trị giảm (cao → thấp)
foreach ($discountsByCategory as $category => $categoryDiscounts) {
    usort($categoryDiscounts, function ($a, $b) use ($orderAmount) {
        $amountA = $a->calculateDiscountAmount($orderAmount);
        $amountB = $b->calculateDiscountAmount($orderAmount);
        return $amountB <=> $amountA; // Descending
    });
}
```

### Bước 3: Greedy Selection

```php
$processedCategories = [];
foreach ($discountsByCategory as $category => $categoryDiscounts) {
    if (in_array($category, $processedCategories)) continue;

    // Áp dụng discount tốt nhất từ category này
    $primaryDiscount = $categoryDiscounts[0];
    if ($primaryDiscount->isValidForOrder($remainingAmount)) {
        // Apply discount
        $appliedDiscounts[] = $primaryDiscount;
        $processedCategories[] = $category;

        // Tìm stackable discounts từ các category khác
        $stackableCategories = Discount::getStackableCategories()[$category];
        foreach ($stackableCategories as $stackableCategory) {
            if (isset($discountsByCategory[$stackableCategory]) &&
                !in_array($stackableCategory, $processedCategories)) {

                $stackableDiscount = $discountsByCategory[$stackableCategory][0];
                if ($stackableDiscount->isValidForOrder($remainingAmount) &&
                    $primaryDiscount->canStackWith($stackableDiscount)) {
                    // Apply stackable discount
                    $appliedDiscounts[] = $stackableDiscount;
                    $processedCategories[] = $stackableCategory;
                }
            }
        }
    }
}
```

### Bước 4: Conflict Detection

```php
// Ghi lại các discount bị loại bỏ và lý do
foreach ($discountsByCategory as $category => $categoryDiscounts) {
    if (!in_array($category, $processedCategories)) {
        foreach ($categoryDiscounts as $discount) {
            $conflicts[] = [
                'discount' => $discount,
                'reason' => "Category {$category} cannot stack with applied categories"
            ];
        }
    }

    // Conflict trong cùng category (chỉ 1 discount/category được chọn)
    for ($i = 1; $i < count($categoryDiscounts); $i++) {
        $conflicts[] = [
            'discount' => $categoryDiscounts[$i],
            'reason' => "Cannot stack with another discount from same category: {$category}"
        ];
    }
}
```

## Ví dụ thực tế

### Kịch bản 1: Stacking thành công

**Input:**

-   Đơn hàng: 2,000,000 VNĐ
-   Discounts: PRODUCT20 (20%, Product), PAYMENT5 (50,000đ, Payment)

**Process:**

1. **Grouping**: Product=[PRODUCT20], Payment=[PAYMENT5]
2. **Selection**: Chọn PRODUCT20 (400,000đ giảm)
3. **Stacking**: PAYMENT5 có thể chồng với Product → áp dụng (50,000đ giảm)

**Output:**

-   ✅ PRODUCT20: -400,000đ (Product category)
-   ✅ PAYMENT5: -50,000đ (Payment category, stacked with Product)
-   **Total discount**: 450,000đ
-   **Final amount**: 1,550,000đ

### Kịch bản 2: Conflict detection

**Input:**

-   Đơn hàng: 1,500,000 VNĐ
-   Discounts: PRODUCT15 (15%, Product), PRODUCT10 (10%, Product), CUSTOMER30 (30,000đ, Customer)

**Process:**

1. **Grouping**: Product=[PRODUCT15, PRODUCT10], Customer=[CUSTOMER30]
2. **Selection**: Chọn PRODUCT15 (225,000đ > 150,000đ của PRODUCT10)
3. **Stacking**: CUSTOMER30 có thể chồng với Product → áp dụng

**Output:**

-   ✅ PRODUCT15: -225,000đ (Product category)
-   ✅ CUSTOMER30: -30,000đ (Customer category, stacked with Product)
-   ❌ PRODUCT10: Conflict - "Cannot stack with another discount from same category: product"
-   **Total discount**: 255,000đ
-   **Final amount**: 1,245,000đ

## Ưu điểm của thuật toán

### 1. **Optimal Selection**

-   Sử dụng Greedy để chọn discount có giá trị cao nhất trong mỗi category
-   Đảm bảo khách hàng nhận được mức giảm giá tối ưu

### 2. **Flexible Stacking Rules**

-   Dễ dàng cấu hình các quy tắc stacking mới
-   Có thể thêm/sửa category và compatibility matrix

### 3. **Transparent Conflicts**

-   Ghi lại đầy đủ lý do tại sao discount bị loại bỏ
-   Giúp khách hàng hiểu rõ quy trình tính toán

### 4. **Business Rules Compliance**

-   Kiểm tra các điều kiện: min_order_amount, usage_limit, validity period
-   Đảm bảo total discount ≤ order amount

## Cấu hình & Customization

### Thêm category mới:

```php
// 1. Update migration
$table->enum('discount_category', [
    'product', 'payment', 'customer', 'seasonal', 'promotion', 'new_category'
]);

// 2. Update stacking rules
public static function getStackableCategories(): array
{
    return [
        'new_category' => ['product', 'payment'], // Có thể chồng với
        'product' => ['payment', 'customer', 'new_category'], // Update existing
        // ...
    ];
}
```

### Thêm logic validation tùy chỉnh:

```php
public function isValidForOrder(float $orderAmount, array $context = []): bool
{
    $baseValid = $this->is_active
                && $this->start_date <= now()
                && $this->end_date >= now()
                && $orderAmount >= $this->min_order_amount
                && ($this->usage_limit === null || $this->used_count < $this->usage_limit);

    // Custom business rules
    if ($this->discount_category === 'customer' && !isset($context['customer_tier'])) {
        return false;
    }

    return $baseValid;
}
```

## Performance Considerations

### Time Complexity:

-   **O(n log n)** cho sorting discounts trong mỗi category
-   **O(k²)** cho stacking checks, với k = số categories
-   **Overall: O(n log n + k²)** với n = total discounts, k = categories

### Space Complexity:

-   **O(n)** cho storage của discount groups và results

### Optimization tips:

1. **Caching**: Cache stackable categories configuration
2. **Indexing**: Index trên discount_category, is_active, validity dates
3. **Eager loading**: Load related OrderDiscount data khi cần thiết
4. **Batch processing**: Xử lý multiple orders cùng lúc

## Testing Scenarios

Hệ thống cung cấp giao diện test tại `/discounts/tools/test-stacking` để:

1. **Functional Testing**: Kiểm tra logic stacking với various combinations
2. **Edge Case Testing**: Min/max amounts, expired discounts, usage limits
3. **Performance Testing**: Large numbers of discount codes
4. **Business Rule Testing**: Category compatibility, conflict resolution

## API Usage

### Basic stacking calculation:

```php
$discountService = app(DiscountService::class);
$result = $discountService->calculateTotalDiscount(
    ['PRODUCT20', 'PAYMENT5', 'CUSTOMER10'],
    2000000
);

// Returns:
[
    'total_discount' => 450000,
    'applied_discounts' => [...],
    'stacking_conflicts' => [...],
    'errors' => [...]
]
```

### Optimal discount discovery:

```php
$optimalResult = $discountService->getOptimalDiscountCombination(
    1500000, // order amount
    ['PRODUCT15', 'PRODUCT10', 'PAYMENT5'] // available codes
);
```

---

**Tác giả**: Laravel E-commerce System  
**Ngày cập nhật**: {{ date('Y-m-d') }}  
**Version**: 1.0
