<?php

namespace App\Services\PricingStrategies;

use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class DatabasePricingStrategy implements PricingStrategyInterface
{
    /**
     * Calculate discount based on customer type from database
     */
    public function calculateDiscount(float $amount, Customer $customer): float
    {
        $customerType = $customer->customerType;
        
        if (!$customerType) {
            Log::warning("Customer {$customer->id} has no customer type assigned");
            return 0;
        }

        // Check minimum order amount requirement
        if ($amount < $customerType->min_order_amount) {
            Log::info("Order amount {$amount} below minimum {$customerType->min_order_amount} for {$customerType->name}");
            return 0;
        }

        $discountPercentage = $customerType->discount_percentage;
        $discountAmount = $amount * ($discountPercentage / 100);

        Log::info("Discount calculated", [
            'customer_id' => $customer->id,
            'customer_type' => $customerType->name,
            'order_amount' => $amount,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount
        ]);

        return round($discountAmount, 2);
    }

    /**
     * Calculate total amount after discount
     */
    public function calculateTotal(float $amount, Customer $customer): float
    {
        $discount = $this->calculateDiscount($amount, $customer);
        $total = $amount - $discount;

        return round($total, 2);
    }

    /**
     * Get strategy name
     */
    public function getName(): string
    {
        return 'database_pricing';
    }
}
