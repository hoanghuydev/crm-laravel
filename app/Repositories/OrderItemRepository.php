<?php

namespace App\Repositories;

use App\Contracts\OrderItemRepositoryInterface;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderItemRepository extends BaseRepository implements OrderItemRepositoryInterface
{
    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }

    public function getByOrder(int $orderId): Collection
    {
        return $this->model->where('order_id', $orderId)->get();
    }

    public function getByProduct(int $productId): Collection
    {
        return $this->model->where('product_id', $productId)->get();
    }

    public function calculateOrderSubtotal(int $orderId): float
    {
        return $this->model->where('order_id', $orderId)->sum('total_price');
    }

    public function getBestSellingProducts(int $limit = 10): Collection
    {
        return $this->model->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                           ->groupBy('product_id')
                           ->orderBy('total_quantity', 'desc')
                           ->limit($limit)
                           ->with('product')
                           ->get();
    }
}
