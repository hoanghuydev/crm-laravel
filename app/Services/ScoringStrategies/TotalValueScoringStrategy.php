<?php

namespace App\Services\ScoringStrategies;

use App\Models\Customer;

class TotalValueScoringStrategy implements ScoringStrategyInterface
{
    /**
     * Calculate normalized score based on total spending
     * Normalization: Score = min(1, totalSpent / maxThreshold)
     */
    public function calculateScore(Customer $customer, array $data = []): float
    {
        $totalSpent = $customer->getTotalSpent();
        
        // Threshold để normalize (10 triệu VND = score 1.0)
        $maxThreshold = $data['max_total_spent'] ?? 10000000; // 10M VND
        
        if ($maxThreshold <= 0) {
            return 0;
        }
        
        // Normalize về 0-1, tối đa là 1.0
        $score = min(1.0, $totalSpent / $maxThreshold);
        
        return round($score, 3);
    }

    public function getWeight(): float
    {
        return 0.35; // 35% weight
    }

    public function getName(): string
    {
        return 'total_value';
    }
}
