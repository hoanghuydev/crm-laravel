<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface OrderDiscountRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get order discounts by order
     */
    public function getByOrder(int $orderId): Collection;

    /**
     * Get order discounts by discount
     */
    public function getByDiscount(int $discountId): Collection;

    /**
     * Calculate total discount amount for order
     */
    public function calculateTotalDiscountAmount(int $orderId): float;

    /**
     * Remove all discounts from order
     */
    public function removeOrderDiscounts(int $orderId): bool;
}
