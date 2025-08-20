<?php

namespace App\Services\ScoringStrategies;

use App\Models\Customer;

class LocationScoringStrategy implements ScoringStrategyInterface
{
    /**
     * Calculate normalized score based on customer location
     * Customers from HCM get higher score
     */
    public function calculateScore(Customer $customer, array $data = []): float
    {
        // Nếu khách hàng ở HCM thì được điểm tối đa
        if ($customer->isFromHCM()) {
            return 1.0;
        }
        
        // Các vùng khác được điểm thấp hơn
        // Có thể mở rộng logic này cho các thành phố khác
        return 0.3; // 30% cho non-HCM locations
    }

    public function getWeight(): float
    {
        return 0.15; // 15% weight
    }

    public function getName(): string
    {
        return 'location';
    }
}
