<?php

namespace App\Repositories;

use App\Contracts\PaymentMethodRepositoryInterface;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getActivePaymentMethods(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Find payment method by name
     */
    public function findByName(string $name, int $excludeId = null): ?Model
    {
        $query = $this->model->where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->first();
    }

    /**
     * Get all payment methods with pagination and search
     */
    public function getAllWithFilters(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->withCount('orders');

        // Search functionality
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find by ID with orders count
     */
    public function findByIdWithOrdersCount(int $id): ?Model
    {
        return $this->model->withCount('orders')->find($id);
    }

    /**
     * Get orders count for payment method
     */
    public function getOrdersCount(int $id): int
    {
        $paymentMethod = $this->model->find($id);
        return $paymentMethod ? $paymentMethod->orders()->count() : 0;
    }

    /**
     * Get payment method statistics
     */
    public function getPaymentMethodStats(int $id): array
    {
        $paymentMethod = $this->model->withCount('orders')->find($id);
        
        return [
            'total_orders' => $paymentMethod->orders_count ?? 0,
            'total_revenue' => $paymentMethod->orders()->sum('total') ?? 0,
        ];
    }
}
