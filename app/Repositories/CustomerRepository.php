<?php

namespace App\Repositories;

use App\Contracts\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function getActiveCustomers(): Collection
    {
        return $this->model->active()->get();
    }

    public function findByEmail(string $email): ?object
    {
        return $this->model->where('email', $email)->first();
    }

    public function getByCustomerType(int $customerTypeId): Collection
    {
        return $this->model->where('customer_type_id', $customerTypeId)->get();
    }

    public function search(string $term): Collection
    {
        return $this->model->where('name', 'like', "%{$term}%")
                           ->orWhere('email', 'like', "%{$term}%")
                           ->get();
    }

    /**
     * Get customers with filtering and pagination
     */
    public function getFilteredCustomers(array $filters = [], int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->with('customerType');

        // Search functionality
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by customer type
        if (!empty($filters['customer_type'])) {
            $query->where('customer_type_id', $filters['customer_type']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
