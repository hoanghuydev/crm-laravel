<?php

namespace App\Services;

use App\Contracts\CustomerRepositoryInterface;
use App\Contracts\CustomerTypeRepositoryInterface;
use App\Services\CustomerScoringService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CustomerService
{
    protected CustomerRepositoryInterface $customerRepository;
    protected CustomerTypeRepositoryInterface $customerTypeRepository;
    protected CustomerScoringService $scoringService;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerTypeRepositoryInterface $customerTypeRepository,
        CustomerScoringService $scoringService
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerTypeRepository = $customerTypeRepository;
        $this->scoringService = $scoringService;
    }

    /**
     * Get all active customers
     */
    public function getAllActiveCustomers(): Collection
    {
        return $this->customerRepository->getActiveCustomers();
    }

    /**
     * Create new customer
     */
    public function createCustomer(array $data): Model
    {
        // Validate customer type exists
        if (isset($data['customer_type_id'])) {
            $customerType = $this->customerTypeRepository->find($data['customer_type_id']);
            if (!$customerType) {
                throw new \Exception('Customer type not found');
            }
        }

        // Check if email already exists
        if (isset($data['email']) && $this->customerRepository->findByEmail($data['email'])) {
            throw new \Exception('Email already exists');
        }

        return $this->customerRepository->create($data);
    }

    /**
     * Update customer
     */
    public function updateCustomer(int $id, array $data): Model
    {
        $customer = $this->customerRepository->findOrFail($id);

        // Validate customer type if being updated
        if (isset($data['customer_type_id'])) {
            $customerType = $this->customerTypeRepository->find($data['customer_type_id']);
            if (!$customerType) {
                throw new \Exception('Customer type not found');
            }
        }

        // Check email uniqueness if being updated
        if (isset($data['email']) && $data['email'] !== $customer->email) {
            if ($this->customerRepository->findByEmail($data['email'])) {
                throw new \Exception('Email already exists');
            }
        }

        return $this->customerRepository->update($customer, $data);
    }

    /**
     * Get customer with discount information
     */
    public function getCustomerWithDiscount(int $id): Model
    {
        $customer = $this->customerRepository->findOrFail($id);
        $customer->load('customerType');
        return $customer;
    }

    /**
     * Search customers
     */
    public function searchCustomers(string $term): Collection
    {
        return $this->customerRepository->search($term);
    }

    /**
     * Get customers by type
     */
    public function getCustomersByType(int $customerTypeId): Collection
    {
        return $this->customerRepository->getByCustomerType($customerTypeId);
    }

    /**
     * Get all customers (including inactive)
     */
    public function getAllCustomers(): Collection
    {
        return $this->customerRepository->all();
    }

    /**
     * Get customers with pagination
     */
    public function getCustomersPaginated(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage);
    }

    /**
     * Deactivate customer (soft delete)
     */
    public function deactivateCustomer(int $id): Model
    {
        $customer = $this->customerRepository->findOrFail($id);
        
        // Check if customer has active orders
        if ($customer->orders()->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])->exists()) {
            throw new \Exception('Cannot deactivate customer with active orders');
        }

        return $this->customerRepository->update($customer, ['is_active' => false]);
    }

    /**
     * Activate customer
     */
    public function activateCustomer(int $id): Model
    {
        $customer = $this->customerRepository->findOrFail($id);
        return $this->customerRepository->update($customer, ['is_active' => true]);
    }

    /**
     * Delete customer permanently
     */
    public function deleteCustomer(int $id): bool
    {
        $customer = $this->customerRepository->findOrFail($id);
        
        // Check if customer has any orders
        if ($customer->orders()->exists()) {
            throw new \Exception('Cannot delete customer with order history. Consider deactivating instead.');
        }

        return $this->customerRepository->delete($customer);
    }

    /**
     * Get customers with filtering and pagination
     */
    public function getFilteredCustomers(array $filters = [], int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->customerRepository->getFilteredCustomers($filters, $perPage);
    }

    /**
     * Get customer with detailed information
     */
    public function getCustomerDetails(int $id): Model
    {
        $customer = $this->customerRepository->findOrFail($id);
        $customer->load(['customerType', 'orders' => function($query) {
            $query->orderBy('order_date', 'desc');
        }]);
        
        return $customer;
    }

    /**
     * Get customer statistics
     */
    public function getCustomerStats(int $id): array
    {
        $customer = $this->customerRepository->findOrFail($id);
        $customer->load(['orders', 'customerType']);
        
        $totalOrders = $customer->orders->count();
        $totalSpent = $customer->orders->whereIn('status', ['delivered', 'shipped'])->sum('total');
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;
        $lastOrderDate = $customer->orders->max('order_date');
        
        return [
            'customer' => $customer,
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'average_order_value' => $averageOrderValue,
            'last_order_date' => $lastOrderDate,
            'discount_percentage' => $customer->getDiscountPercentage(),
        ];
    }

    /**
     * Get customer score breakdown
     */
    public function getCustomerScoreBreakdown(int $id): array
    {
        $customer = $this->customerRepository->findOrFail($id);
        return $this->scoringService->getCustomerScoreBreakdown($customer);
    }

    /**
     * Check if customer needs score recalculation
     */
    public function needsScoreRecalculation(int $id): bool
    {
        $customer = $this->customerRepository->findOrFail($id);
        return $this->scoringService->shouldRecalculateScore($customer);
    }

    /**
     * Get customer with full scoring information
     */
    public function getCustomerWithScoring(int $id): Model
    {
        $customer = $this->customerRepository->findOrFail($id);
        $customer->load('customerType');
        
        // Add computed scoring data
        $customer->total_spent_computed = $customer->getTotalSpent();
        $customer->total_orders_computed = $customer->getTotalOrderCount();
        $customer->avg_days_between_orders = $customer->getAverageDaysBetweenOrders();
        $customer->is_from_hcm = $customer->isFromHCM();
        $customer->score_breakdown = $customer->getScoreBreakdown();
        
        return $customer;
    }
}
