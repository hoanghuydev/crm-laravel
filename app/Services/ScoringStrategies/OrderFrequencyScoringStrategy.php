<?php

namespace App\Services\ScoringStrategies;

use App\Models\Customer;

class OrderFrequencyScoringStrategy implements ScoringStrategyInterface
{
    /**
     * Calculate normalized score based on order frequency
     * Higher frequency (shorter average days between orders) = higher score
     * Normalization: Score = max(0, 1 - (avgDays / maxDaysThreshold))
     */
    public function calculateScore(Customer $customer, array $data = []): float
    {
        $avgDaysBetweenOrders = $customer->getAverageDaysBetweenOrders();
        
        // Nếu chưa có đủ đơn hàng để tính frequency
        if ($avgDaysBetweenOrders <= 0) {
            return 0;
        }
        
        // Threshold: 30 ngày = score 0.5, 7 ngày = score gần 1.0
        $maxDaysThreshold = $data['max_avg_days'] ?? 60; // 60 ngày = score 0
        
        if ($maxDaysThreshold <= 0) {
            return 0;
        }
        
        // Invert the score: ít ngày hơn = điểm cao hơn
        $score = max(0, 1 - ($avgDaysBetweenOrders / $maxDaysThreshold));
        
        return round($score, 3);
    }

    public function getWeight(): float
    {
        return 0.25; // 25% weight
    }

    public function getName(): string
    {
        return 'order_frequency';
    }
}
