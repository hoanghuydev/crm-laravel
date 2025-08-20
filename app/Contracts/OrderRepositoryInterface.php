<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?object;

    /**
     * Get orders by customer
     */
    public function getByCustomer(int $customerId): Collection;

    /**
     * Get orders by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get orders by date range
     */
    public function getByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Generate unique order number
     */
    public function generateOrderNumber(): string;

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $limit = 10): Collection;

    /**
     * Calculate total revenue for date range
     */
    public function getTotalRevenue(string $startDate, string $endDate): float;
}
