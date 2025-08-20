<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CustomerTypeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active customer types
     */
    public function getActiveCustomerTypes(): Collection;

    /**
     * Find customer type by name
     */
    public function findByName(string $name): ?object;
}
