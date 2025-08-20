<?php

namespace App\Repositories;

use App\Contracts\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByOrderNumber(string $orderNumber): ?object
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    public function getByCustomer(int $customerId): Collection
    {
        return $this->model->where('customer_id', $customerId)
                           ->orderBy('order_date', 'desc')
                           ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
                           ->orderBy('order_date', 'desc')
                           ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('order_date', [$startDate, $endDate])
                           ->orderBy('order_date', 'desc')
                           ->get();
    }

    public function generateOrderNumber(): string
    {
        return Order::generateOrderNumber();
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return $this->model->orderBy('created_at', 'desc')
                           ->limit($limit)
                           ->get();
    }

    public function getTotalRevenue(string $startDate, string $endDate): float
    {
        return $this->model->whereBetween('order_date', [$startDate, $endDate])
                           ->whereIn('status', ['delivered', 'shipped'])
                           ->sum('total');
    }
}
