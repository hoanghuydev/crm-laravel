<?php

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActiveProducts(): Collection
    {
        return $this->model->active()->get();
    }

    public function getInStockProducts(): Collection
    {
        return $this->model->inStock()->get();
    }

    public function findBySku(string $sku): ?object
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function searchByName(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }

    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->model->where('quantity_in_stock', '<=', $threshold)
                           ->where('status', '!=', 'inactive')
                           ->get();
    }

    public function updateStock(int $productId, int $quantity): bool
    {
        return $this->model->where('id', $productId)
                           ->update(['quantity_in_stock' => $quantity]) > 0;
    }

    /**
     * Get all products with pagination and search
     */
    public function getAllWithFilters(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->query();

        // Search functionality
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by stock status
        if (!empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'in_stock') {
                $query->where('quantity_in_stock', '>', 0);
            } elseif ($filters['stock_status'] === 'low_stock') {
                $query->where('quantity_in_stock', '>', 0)->where('quantity_in_stock', '<=', 10);
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $query->where('quantity_in_stock', '=', 0);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
