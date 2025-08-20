<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Order;
use App\Models\Product;
use App\Models\PaymentMethod;

class TestCustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get customer types
        $regularType = CustomerType::where('name', 'Khách Hàng Thường')->first();
        $bronzeType = CustomerType::where('name', 'Khách Hàng Đồng')->first();
        $silverType = CustomerType::where('name', 'Khách Hàng Bạc')->first();

        if (!$regularType || !$bronzeType || !$silverType) {
            $this->command->error('Customer types not found. Run CustomerTypeScoringSeeder first.');
            return;
        }

        // Get or create a payment method
        $paymentMethod = PaymentMethod::first();
        if (!$paymentMethod) {
            $paymentMethod = PaymentMethod::create([
                'name' => 'Tiền mặt',
                'is_active' => true,
            ]);
        }

        // Get or create some products
        $products = Product::take(3)->get();
        if ($products->count() < 3) {
            for ($i = $products->count(); $i < 3; $i++) {
                Product::create([
                    'name' => 'Sản phẩm test ' . ($i + 1),
                    'price' => 100000 + ($i * 50000),
                    'quantity_in_stock' => 100,
                    'is_active' => true,
                ]);
            }
            $products = Product::take(3)->get();
        }

        // Create test customers with different scenarios
        $testCustomers = [
            [
                'name' => 'Khách Hàng Mới',
                'email' => 'new.customer@test.com',
                'phone' => '0901234567',
                'address' => 'Hà Nội',
                'customer_type_id' => $regularType->id,
                'joined_at' => now()->subDays(7),
                'is_active' => true,
                'orders_to_create' => 0, // No orders
            ],
            [
                'name' => 'Khách Hàng HCM',
                'email' => 'hcm.customer@test.com',
                'phone' => '0901234568',
                'address' => 'Quận 1, HCM',
                'customer_type_id' => $regularType->id,
                'joined_at' => now()->subDays(30),
                'is_active' => true,
                'orders_to_create' => 2, // Should get some score from location + orders
            ],
            [
                'name' => 'Khách Hàng Tích Cực',
                'email' => 'active.customer@test.com',
                'phone' => '0901234569',
                'address' => 'Ho Chi Minh City',
                'customer_type_id' => $regularType->id,
                'joined_at' => now()->subDays(60),
                'is_active' => true,
                'orders_to_create' => 5, // Should reach Bronze or Silver
            ],
            [
                'name' => 'Khách Hàng VIP Test',
                'email' => 'vip.customer@test.com',
                'phone' => '0901234570',
                'address' => 'Sài Gòn, Việt Nam',
                'customer_type_id' => $regularType->id,
                'joined_at' => now()->subDays(90),
                'is_active' => true,
                'orders_to_create' => 10, // Should reach high tier
            ],
        ];

        foreach ($testCustomers as $customerData) {
            $ordersToCreate = $customerData['orders_to_create'];
            unset($customerData['orders_to_create']);

            $customer = Customer::create($customerData);
            $this->command->info("Created customer: {$customer->name}");

            // Create orders for this customer
            if ($ordersToCreate > 0) {
                $orderDates = $this->generateOrderDates($customer->joined_at, $ordersToCreate);
                
                foreach ($orderDates as $orderDate) {
                    $randomProduct = $products->random();
                    $quantity = rand(1, 3);
                    $total = $randomProduct->price * $quantity;

                    $order = Order::create([
                        'customer_id' => $customer->id,
                        'payment_method_id' => $paymentMethod->id,
                        'order_number' => 'TEST-' . time() . '-' . $customer->id . '-' . rand(100, 999),
                        'subtotal' => $total,
                        'customer_discount_amount' => 0,
                        'discount_amount' => 0,
                        'total' => $total,
                        'status' => 'delivered', // Completed orders for scoring
                        'order_date' => $orderDate,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);

                    // Update customer's last order date
                    $customer->update(['last_order_at' => $orderDate]);
                }

                $this->command->info("  - Created {$ordersToCreate} orders");
            }
        }

        $this->command->info('Test customers created successfully!');
        $this->command->info('Run the scoring service to calculate scores and reclassify customers.');
    }

    /**
     * Generate order dates spread over time since joining
     */
    private function generateOrderDates($joinedAt, $count): array
    {
        $dates = [];
        $daysSinceJoining = now()->diffInDays($joinedAt);
        
        for ($i = 0; $i < $count; $i++) {
            // Spread orders over the period since joining
            $daysAgo = rand(1, $daysSinceJoining);
            $dates[] = now()->subDays($daysAgo);
        }

        // Sort dates chronologically
        sort($dates);
        
        return $dates;
    }
}
