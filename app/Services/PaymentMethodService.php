<?php

namespace App\Services;

use App\Contracts\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodService
{
    protected PaymentMethodRepositoryInterface $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * Get all payment methods with pagination and search
     */
    public function getAllPaymentMethods(array $filters = [], int $perPage = 15)
    {
        return $this->paymentMethodRepository->getAllWithFilters($filters, $perPage);
    }

    /**
     * Get all active payment methods
     */
    public function getAllActivePaymentMethods(): Collection
    {
        return $this->paymentMethodRepository->getActivePaymentMethods();
    }

    /**
     * Create new payment method
     */
    public function createPaymentMethod(array $data): Model
    {
        // Validate business rules
        $this->validatePaymentMethodData($data);
        
        return $this->paymentMethodRepository->create($data);
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(int $id, array $data): Model
    {
        $paymentMethod = $this->paymentMethodRepository->findOrFail($id);

        // Validate business rules
        $this->validatePaymentMethodData($data, $id);
        
        return $this->paymentMethodRepository->update($paymentMethod, $data);
    }

    /**
     * Get payment method by ID with orders count
     */
    public function getPaymentMethodById(int $id): Model
    {
        $paymentMethod = $this->paymentMethodRepository->findByIdWithOrdersCount($id);
        if (!$paymentMethod) {
            throw new \Exception('Payment method not found');
        }
        return $paymentMethod;
    }

    /**
     * Delete or deactivate payment method
     */
    public function deletePaymentMethod(int $id): bool
    {
        $paymentMethod = $this->paymentMethodRepository->findOrFail($id);
        
        // Check if payment method is used in orders
        $ordersCount = $this->paymentMethodRepository->getOrdersCount($id);
        if ($ordersCount > 0) {
            // Deactivate instead of delete if it has orders
            $this->paymentMethodRepository->update($paymentMethod, ['is_active' => false]);
            return false; // Indicates deactivated, not deleted
        } else {
            // Safe to delete if no orders
            return $this->paymentMethodRepository->delete($paymentMethod);
        }
    }

    /**
     * Toggle payment method status
     */
    public function togglePaymentMethodStatus(int $id): Model
    {
        $paymentMethod = $this->paymentMethodRepository->findOrFail($id);
        
        return $this->paymentMethodRepository->update($paymentMethod, [
            'is_active' => !$paymentMethod->is_active
        ]);
    }

    /**
     * Get payment method statistics
     */
    public function getPaymentMethodStats(int $id): array
    {
        return $this->paymentMethodRepository->getPaymentMethodStats($id);
    }

    /**
     * Validate payment method data
     */
    private function validatePaymentMethodData(array $data, int $excludeId = null): void
    {
        // Check unique name
        if (isset($data['name'])) {
            $existing = $this->paymentMethodRepository->findByName($data['name'], $excludeId);
            if ($existing) {
                throw new \Exception('Payment method name already exists');
            }
        }

        // Validate required fields
        if (isset($data['name']) && empty(trim($data['name']))) {
            throw new \Exception('Payment method name is required');
        }
    }
}
