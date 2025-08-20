<?php

namespace App\Services;

use App\Contracts\CustomerTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CustomerTypeService
{
    protected CustomerTypeRepositoryInterface $customerTypeRepository;

    public function __construct(CustomerTypeRepositoryInterface $customerTypeRepository)
    {
        $this->customerTypeRepository = $customerTypeRepository;
    }

    /**
     * Get all active customer types
     */
    public function getAllActiveCustomerTypes(): Collection
    {
        return $this->customerTypeRepository->getActiveCustomerTypes();
    }

    /**
     * Get all customer types
     */
    public function getAllCustomerTypes(): Collection
    {
        return $this->customerTypeRepository->all();
    }

    /**
     * Create new customer type
     */
    public function createCustomerType(array $data): Model
    {
        // Check if name already exists
        if ($this->customerTypeRepository->findByName($data['name'])) {
            throw new \Exception('Customer type name already exists');
        }

        return $this->customerTypeRepository->create($data);
    }

    /**
     * Update customer type
     */
    public function updateCustomerType(int $id, array $data): Model
    {
        $customerType = $this->customerTypeRepository->findOrFail($id);

        // Check name uniqueness if being updated
        if (isset($data['name']) && $data['name'] !== $customerType->name) {
            if ($this->customerTypeRepository->findByName($data['name'])) {
                throw new \Exception('Customer type name already exists');
            }
        }

        return $this->customerTypeRepository->update($customerType, $data);
    }

    /**
     * Get customer type by ID
     */
    public function getCustomerType(int $id): Model
    {
        return $this->customerTypeRepository->findOrFail($id);
    }

    /**
     * Deactivate customer type
     */
    public function deactivateCustomerType(int $id): Model
    {
        $customerType = $this->customerTypeRepository->findOrFail($id);
        
        // Check if there are customers using this type
        if ($customerType->customers()->where('is_active', true)->exists()) {
            throw new \Exception('Cannot deactivate customer type with active customers');
        }

        return $this->customerTypeRepository->update($customerType, ['is_active' => false]);
    }

    /**
     * Activate customer type
     */
    public function activateCustomerType(int $id): Model
    {
        $customerType = $this->customerTypeRepository->findOrFail($id);
        return $this->customerTypeRepository->update($customerType, ['is_active' => true]);
    }

    /**
     * Delete customer type
     */
    public function deleteCustomerType(int $id): bool
    {
        $customerType = $this->customerTypeRepository->findOrFail($id);
        
        // Check if there are customers using this type
        if ($customerType->customers()->exists()) {
            throw new \Exception('Cannot delete customer type with existing customers');
        }

        return $this->customerTypeRepository->delete($customerType);
    }
}
