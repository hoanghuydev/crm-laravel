<?php

namespace App\Services;

use App\Models\Customer;
use App\Services\PricingStrategies\PricingStrategyInterface;
use App\Services\PricingStrategies\DatabasePricingStrategy;

class PricingStrategyFactory
{
    /**
     * Create appropriate pricing strategy for customer
     * Currently uses database-driven approach for extensibility
     */
    public static function create(Customer $customer): PricingStrategyInterface
    {
        // For now, we use database pricing for all customers
        // This can be extended to support different strategies per customer type
        // or other criteria in the future
        
        return new DatabasePricingStrategy();
    }

    /**
     * Create specific pricing strategy by name
     * Useful for testing or specific use cases
     */
    public static function createByName(string $strategyName): PricingStrategyInterface
    {
        switch ($strategyName) {
            case 'database':
            default:
                return new DatabasePricingStrategy();
        }
    }
}
