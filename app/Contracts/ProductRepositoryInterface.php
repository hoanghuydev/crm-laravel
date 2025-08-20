<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active products
     */
    public function getActiveProducts(): Collection;

    /**
     * Get products in stock
     */
    public function getInStockProducts(): Collection;

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku): ?object;

    /**
     * Search products by name
     */
    public function searchByName(string $name): Collection;

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection;

    /**
     * Update product stock
     */
    public function updateStock(int $productId, int $quantity): bool;
}
