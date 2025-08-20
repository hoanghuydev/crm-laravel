<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DiscountRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active discounts
     */
    public function getActiveDiscounts(): Collection;

    /**
     * Get valid discounts for current date
     */
    public function getValidDiscounts(): Collection;

    /**
     * Find discount by code
     */
    public function findByCode(string $code): ?object;

    /**
     * Get stackable discounts
     */
    public function getStackableDiscounts(): Collection;

    /**
     * Get discounts that can be applied to order amount
     */
    public function getApplicableDiscounts(float $orderAmount): Collection;

    /**
     * Increment discount usage
     */
    public function incrementUsage(int $discountId): bool;
}
