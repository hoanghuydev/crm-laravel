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

    /**
     * Get orders with filtering and pagination
     */
    public function getFilteredOrders(array $filters = [], int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->with(['customer', 'paymentMethod', 'orderItems']);

        // Search functionality
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by customer
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('order_date', 'desc')->paginate($perPage);
    }
}
