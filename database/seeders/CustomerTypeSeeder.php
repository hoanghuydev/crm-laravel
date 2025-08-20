<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomerType;

class CustomerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerTypes = [
            [
                'name' => 'Regular',
                'description' => 'Standard customers with no special benefits',
                'discount_percentage' => 0.00,
                'min_order_amount' => 0.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bronze',
                'description' => 'Bronze tier customers with basic benefits',
                'discount_percentage' => 2.50,
                'min_order_amount' => 500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Silver',
                'description' => 'Silver tier customers with enhanced benefits',
                'discount_percentage' => 5.00,
                'min_order_amount' => 1000000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Gold',
                'description' => 'Gold tier customers with premium benefits',
                'discount_percentage' => 7.50,
                'min_order_amount' => 1500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'VIP',
                'description' => 'VIP customers with exclusive benefits and priority support',
                'discount_percentage' => 10.00,
                'min_order_amount' => 2000000.00,
                'is_active' => true,
            ],
        ];

        foreach ($customerTypes as $type) {
            CustomerType::create($type);
        }
    }
}
