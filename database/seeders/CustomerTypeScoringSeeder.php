<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerType;

class CustomerTypeScoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        CustomerType::query()->delete();

        $customerTypes = [
            [
                'name' => 'Khách Hàng Thường',
                'description' => 'Khách hàng mới hoặc ít mua hàng',
                'discount_percentage' => 0,
                'min_order_amount' => 0,
                'minimum_score' => 0,
                'priority' => 1, // Lowest priority
                'scoring_weights' => null, // Use default weights
                'is_active' => true,
            ],
            [
                'name' => 'Khách Hàng Đồng',
                'description' => 'Khách hàng có ít hoạt động mua sắm',
                'discount_percentage' => 3,
                'min_order_amount' => 300000,
                'minimum_score' => 0.15, // Low threshold for easy testing
                'priority' => 2,
                'scoring_weights' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Khách Hàng Bạc',
                'description' => 'Khách hàng trung bình',
                'discount_percentage' => 5,
                'min_order_amount' => 200000,
                'minimum_score' => 0.3, // Medium-low threshold
                'priority' => 3,
                'scoring_weights' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Khách Hàng Vàng',
                'description' => 'Khách hàng tích cực',
                'discount_percentage' => 8,
                'min_order_amount' => 100000,
                'minimum_score' => 0.5, // Medium threshold
                'priority' => 4,
                'scoring_weights' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Khách Hàng VIP',
                'description' => 'Khách hàng thân thiết với hoạt động mua sắm cao',
                'discount_percentage' => 12,
                'min_order_amount' => 50000,
                'minimum_score' => 0.7, // High threshold but achievable
                'priority' => 5,
                'scoring_weights' => [
                    'total_value_weight' => 0.4,      // Increase weight for VIP
                    'order_count_weight' => 0.3,
                    'order_frequency_weight' => 0.2,
                    'location_weight' => 0.1,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Khách Hàng Premium',
                'description' => 'Khách hàng cao cấp với ưu đãi tối đa',
                'discount_percentage' => 15,
                'min_order_amount' => 0,
                'minimum_score' => 0.85, // Very high threshold
                'priority' => 6, // Highest priority
                'scoring_weights' => [
                    'total_value_weight' => 0.45,     // Heavy weight on spending
                    'order_count_weight' => 0.3,
                    'order_frequency_weight' => 0.15,
                    'location_weight' => 0.1,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($customerTypes as $type) {
            CustomerType::create($type);
        }

        $this->command->info('Customer types with scoring thresholds seeded successfully!');
        $this->command->info('Score thresholds are set low for easy testing:');
        $this->command->info('- Đồng: 0.15 (15% score)');
        $this->command->info('- Bạc: 0.30 (30% score)');
        $this->command->info('- Vàng: 0.50 (50% score)');
        $this->command->info('- VIP: 0.70 (70% score)');
        $this->command->info('- Premium: 0.85 (85% score)');
    }
}
