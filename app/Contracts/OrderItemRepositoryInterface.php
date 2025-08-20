<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get order items by order
     */
    public function getByOrder(int $orderId): Collection;

    /**
     * Get order items by product
     */
    public function getByProduct(int $productId): Collection;

    /**
     * Calculate order subtotal
     */
    public function calculateOrderSubtotal(int $orderId): float;

    /**
     * Get best selling products
     */
    public function getBestSellingProducts(int $limit = 10): Collection;
}
