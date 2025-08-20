<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active customers
     */
    public function getActiveCustomers(): Collection;

    /**
     * Find customer by email
     */
    public function findByEmail(string $email): ?object;

    /**
     * Get customers by customer type
     */
    public function getByCustomerType(int $customerTypeId): Collection;

    /**
     * Search customers by name or email
     */
    public function search(string $term): Collection;
}
