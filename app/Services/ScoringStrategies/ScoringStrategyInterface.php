<?php

namespace App\Services\ScoringStrategies;

use App\Models\Customer;

interface ScoringStrategyInterface
{
    /**
     * Calculate normalized score for a specific metric
     */
    public function calculateScore(Customer $customer, array $data = []): float;

    /**
     * Get the weight of this scoring strategy
     */
    public function getWeight(): float;

    /**
     * Get the name of this scoring strategy
     */
    public function getName(): string;
}
