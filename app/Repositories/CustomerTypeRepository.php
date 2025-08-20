<?php

namespace App\Repositories;

use App\Contracts\CustomerTypeRepositoryInterface;
use App\Models\CustomerType;
use Illuminate\Database\Eloquent\Collection;

class CustomerTypeRepository extends BaseRepository implements CustomerTypeRepositoryInterface
{
    public function __construct(CustomerType $model)
    {
        parent::__construct($model);
    }

    public function getActiveCustomerTypes(): Collection
    {
        return $this->model->active()->get();
    }

    public function findByName(string $name): ?object
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Get all customer types with pagination and search
     */
    public function getAllWithFilters(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->withCount('customers');

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
     * Find by ID with customers count
     */
    public function findByIdWithCustomersCount(int $id): ?object
    {
        return $this->model->withCount('customers')->find($id);
    }
}
