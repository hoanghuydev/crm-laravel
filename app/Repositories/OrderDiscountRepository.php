<?php

namespace App\Repositories;

use App\Contracts\OrderDiscountRepositoryInterface;
use App\Models\OrderDiscount;
use Illuminate\Database\Eloquent\Collection;

class OrderDiscountRepository extends BaseRepository implements OrderDiscountRepositoryInterface
{
    public function __construct(OrderDiscount $model)
    {
        parent::__construct($model);
    }

    public function getByOrder(int $orderId): Collection
    {
        return $this->model->where('order_id', $orderId)->get();
    }

    public function getByDiscount(int $discountId): Collection
    {
        return $this->model->where('discount_id', $discountId)->get();
    }

    public function calculateTotalDiscountAmount(int $orderId): float
    {
        return $this->model->where('order_id', $orderId)->sum('discount_amount');
    }

    public function removeOrderDiscounts(int $orderId): bool
    {
        return $this->model->where('order_id', $orderId)->delete() > 0;
    }
}
