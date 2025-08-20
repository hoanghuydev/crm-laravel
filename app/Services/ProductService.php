<?php

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all active products
     */
    public function getAllActiveProducts(): Collection
    {
        return $this->productRepository->getActiveProducts();
    }

    /**
     * Get products in stock
     */
    public function getInStockProducts(): Collection
    {
        return $this->productRepository->getInStockProducts();
    }

    /**
     * Get all available products (active and in stock)
     */
    public function getAllAvailableProducts(): Collection
    {
        return $this->productRepository->getInStockProducts();
    }

    /**
     * Create new product
     */
    public function createProduct(array $data): Model
    {
        // Check SKU uniqueness if provided
        if (isset($data['sku']) && $this->productRepository->findBySku($data['sku'])) {
            throw new \Exception('SKU already exists');
        }

        return $this->productRepository->create($data);
    }

    /**
     * Update product
     */
    public function updateProduct(int $id, array $data): Model
    {
        $product = $this->productRepository->findOrFail($id);

        // Check SKU uniqueness if being updated
        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            if ($this->productRepository->findBySku($data['sku'])) {
                throw new \Exception('SKU already exists');
            }
        }

        return $this->productRepository->update($product, $data);
    }

    /**
     * Check product availability
     */
    public function checkAvailability(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findOrFail($productId);
        return $product->isAvailable() && $product->quantity_in_stock >= $quantity;
    }

    /**
     * Reduce product stock
     */
    public function reduceStock(int $productId, int $quantity): void
    {
        $product = $this->productRepository->findOrFail($productId);
        
        if ($product->quantity_in_stock < $quantity) {
            throw new \Exception('Insufficient stock');
        }

        $product->reduceStock($quantity);
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->productRepository->getLowStockProducts($threshold);
    }

    /**
     * Search products by name
     */
    public function searchProducts(string $name): Collection
    {
        return $this->productRepository->searchByName($name);
    }

    /**
     * Get all products with pagination and filters
     */
    public function getAllProducts(array $filters = [], int $perPage = 15)
    {
        return $this->productRepository->getAllWithFilters($filters, $perPage);
    }
}
