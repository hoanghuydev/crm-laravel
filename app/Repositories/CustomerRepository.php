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
}
