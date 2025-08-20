<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Discount;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Payment Methods
        $paymentMethods = [
            ['name' => 'Cash', 'description' => 'Cash payment', 'is_active' => true],
            ['name' => 'Credit Card', 'description' => 'Credit card payment', 'is_active' => true],
            ['name' => 'Bank Transfer', 'description' => 'Bank transfer payment', 'is_active' => true],
            ['name' => 'E-wallet', 'description' => 'Electronic wallet payment', 'is_active' => true],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        // Sample Customers
        $customerTypeIds = CustomerType::pluck('id')->toArray();
        
        $customers = [
            [
                'customer_type_id' => $customerTypeIds[0], // Regular
                'name' => 'Nguyễn Văn An',
                'email' => 'nguyenvanan@example.com',
                'phone' => '0901234567',
                'address' => '123 Lê Lợi, Quận 1, TP.HCM',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'is_active' => true,
            ],
            [
                'customer_type_id' => $customerTypeIds[1], // Bronze
                'name' => 'Trần Thị Bình',
                'email' => 'tranthibinh@example.com',
                'phone' => '0912345678',
                'address' => '456 Nguyễn Huệ, Quận 1, TP.HCM',
                'date_of_birth' => '1985-08-22',
                'gender' => 'female',
                'is_active' => true,
            ],
            [
                'customer_type_id' => $customerTypeIds[2], // Silver
                'name' => 'Lê Hoàng Công',
                'email' => 'lehoangcong@example.com',
                'phone' => '0923456789',
                'address' => '789 Hai Bà Trưng, Quận 3, TP.HCM',
                'date_of_birth' => '1988-12-10',
                'gender' => 'male',
                'is_active' => true,
            ],
            [
                'customer_type_id' => $customerTypeIds[3], // Gold
                'name' => 'Phạm Thị Dung',
                'email' => 'phamthidung@example.com',
                'phone' => '0934567890',
                'address' => '321 Võ Văn Tần, Quận 3, TP.HCM',
                'date_of_birth' => '1992-03-18',
                'gender' => 'female',
                'is_active' => true,
            ],
            [
                'customer_type_id' => $customerTypeIds[4], // VIP
                'name' => 'Đỗ Minh Châu',
                'email' => 'dominchau@example.com',
                'phone' => '0945678901',
                'address' => '654 Pasteur, Quận 1, TP.HCM',
                'date_of_birth' => '1980-07-25',
                'gender' => 'male',
                'is_active' => true,
            ],
            [
                'customer_type_id' => $customerTypeIds[0], // Regular
                'name' => 'Vũ Thị Hoa',
                'email' => 'vuthihoa@example.com',
                'phone' => '0956789012',
                'address' => '987 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM',
                'date_of_birth' => '1995-11-30',
                'gender' => 'female',
                'is_active' => false, // Inactive customer for testing
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Sample Products
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with Pro features',
                'price' => 25000000.00,
                'quantity_in_stock' => 50,
                'sku' => 'IP15PRO',
                'status' => 'active',
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Premium Android smartphone',
                'price' => 20000000.00,
                'quantity_in_stock' => 30,
                'sku' => 'SGS24',
                'status' => 'active',
            ],
            [
                'name' => 'MacBook Air M2',
                'description' => 'Lightweight laptop with M2 chip',
                'price' => 35000000.00,
                'quantity_in_stock' => 25,
                'sku' => 'MBAM2',
                'status' => 'active',
            ],
            [
                'name' => 'iPad Pro 12.9"',
                'description' => 'Professional tablet for creative work',
                'price' => 30000000.00,
                'quantity_in_stock' => 0,
                'sku' => 'IPADPRO129',
                'status' => 'out_of_stock',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Sample Discounts
        $discounts = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome Discount',
                'description' => '10% discount for new customers',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 1000000.00,
                'max_discount_amount' => 500000.00,
                'usage_limit' => 100,
                'used_count' => 15,
                'can_stack' => true,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(30),
                'is_active' => true,
            ],
            [
                'code' => 'SAVE500K',
                'name' => 'Fixed Amount Discount',
                'description' => '500,000 VND off on orders over 5M',
                'type' => 'fixed_amount',
                'value' => 500000.00,
                'min_order_amount' => 5000000.00,
                'max_discount_amount' => null,
                'usage_limit' => 50,
                'used_count' => 8,
                'can_stack' => false,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(45),
                'is_active' => true,
            ],
            [
                'code' => 'BLACKFRIDAY',
                'name' => 'Black Friday Sale',
                'description' => '20% discount for Black Friday',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 2000000.00,
                'max_discount_amount' => 1000000.00,
                'usage_limit' => null,
                'used_count' => 45,
                'can_stack' => true,
                'start_date' => now()->subDays(60),
                'end_date' => now()->subDays(30), // Expired
                'is_active' => false,
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::create($discount);
        }
    }
}
