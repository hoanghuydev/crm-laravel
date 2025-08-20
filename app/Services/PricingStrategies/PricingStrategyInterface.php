<?php

namespace App\Services\PricingStrategies;

use App\Models\Customer;

interface PricingStrategyInterface
{
    /**
     * Calculate discount amount for a customer
     */
    public function calculateDiscount(float $amount, Customer $customer): float;

    /**
     * Calculate final total after discount
     */
    public function calculateTotal(float $amount, Customer $customer): float;

    /**
     * Get strategy name for logging/debugging
     */
    public function getName(): string;
}
