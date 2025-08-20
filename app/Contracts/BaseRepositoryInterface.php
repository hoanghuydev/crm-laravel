<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all(): Collection;

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model;

    /**
     * Find a record by ID or throw exception
     */
    public function findOrFail(int $id): Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a record
     */
    public function delete(Model $model): bool;

    /**
     * Find records by criteria
     */
    public function findBy(array $criteria): Collection;

    /**
     * Find first record by criteria
     */
    public function findOneBy(array $criteria): ?Model;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $criteria = []): LengthAwarePaginator;

    /**
     * Get records count
     */
    public function count(array $criteria = []): int;
}
