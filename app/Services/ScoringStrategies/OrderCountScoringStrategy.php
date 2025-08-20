<?php

namespace App\Services\ScoringStrategies;

use App\Models\Customer;

class OrderCountScoringStrategy implements ScoringStrategyInterface
{
    /**
     * Calculate normalized score based on total number of orders
     * Normalization: Score = min(1, orderCount / maxThreshold)
     */
    public function calculateScore(Customer $customer, array $data = []): float
    {
        $orderCount = $customer->getTotalOrderCount();
        
        // Threshold để normalize (20 đơn hàng = score 1.0)
        $maxThreshold = $data['max_order_count'] ?? 20;
        
        if ($maxThreshold <= 0) {
            return 0;
        }
        
        // Normalize về 0-1, tối đa là 1.0
        $score = min(1.0, $orderCount / $maxThreshold);
        
        return round($score, 3);
    }

    public function getWeight(): float
    {
        return 0.25; // 25% weight
    }

    public function getName(): string
    {
        return 'order_count';
    }
}
