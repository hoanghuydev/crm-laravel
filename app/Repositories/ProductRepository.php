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
}
