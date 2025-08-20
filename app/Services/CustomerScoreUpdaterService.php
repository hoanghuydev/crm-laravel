<?php

namespace App\Services;

use App\DTOs\CustomerScoringResultDTO;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for updating customer scores and handling related operations
 * Follows SRP - only handles customer score updates and related logging
 */
class CustomerScoreUpdaterService
{
    public function __construct(
        protected CustomerScoringService $scoringService
    ) {}

    /**
     * Update customer score after order creation
     */
    public function updateAfterOrder(Order $order): CustomerScoringResultDTO
    {
        $customer = $order->customer;
        
        Log::info("Starting score update for customer {$customer->id} after order {$order->id}");

        try {
            // Capture current state
            $oldScore = $customer->current_score ?? 0;
            $oldType = $customer->customerType;

            // Update customer's last order date
            $customer->update(['last_order_at' => $order->created_at]);

            // Recalculate and reclassify customer
            $wasReclassified = $this->scoringService->reclassifyCustomer($customer);
            $customer->refresh();

            $result = CustomerScoringResultDTO::success(
                customer: $customer,
                oldScore: $oldScore,
                newScore: (float) $customer->current_score,
                oldType: $oldType,
                newType: $customer->customerType,
                wasReclassified: $wasReclassified
            );

            $this->logSuccess($result);
            
            return $result;

        } catch (\Throwable $e) {
            $result = CustomerScoringResultDTO::error($customer, $e);
            $this->logError($result, ['order_id' => $order->id]);
            
            return $result;
        }
    }

    /**
     * Update customer score manually
     */
    public function updateCustomer(Customer $customer, bool $includeBreakdown = false): CustomerScoringResultDTO
    {
        Log::info("Starting manual score update for customer {$customer->id}");

        try {
            // Capture current state
            $oldScore = $customer->current_score ?? 0;
            $oldType = $customer->customerType;

            // Get breakdown if requested
            $scoreBreakdown = $includeBreakdown 
                ? $this->scoringService->getCustomerScoreBreakdown($customer)
                : [];

            // Recalculate and reclassify customer
            $wasReclassified = $this->scoringService->reclassifyCustomer($customer);
            $customer->refresh();

            $result = CustomerScoringResultDTO::success(
                customer: $customer,
                oldScore: $oldScore,
                newScore: (float) $customer->current_score,
                oldType: $oldType,
                newType: $customer->customerType,
                wasReclassified: $wasReclassified,
                scoreBreakdown: $scoreBreakdown
            );

            $this->logSuccess($result);
            
            return $result;

        } catch (\Throwable $e) {
            $result = CustomerScoringResultDTO::error($customer, $e);
            $this->logError($result);
            
            return $result;
        }
    }

    /**
     * Batch update all customers
     */
    public function updateAllCustomers(): array
    {
        Log::info("Starting batch customer score update");

        $customers = Customer::active()->get();
        $results = [
            'total' => $customers->count(),
            'successful' => 0,
            'reclassified' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($customers as $customer) {
            $result = $this->updateCustomer($customer);
            
            if ($result->isSuccessful()) {
                $results['successful']++;
                if ($result->wasReclassified) {
                    $results['reclassified']++;
                }
            } else {
                $results['errors']++;
            }
            
            $results['details'][] = $result;
        }

        Log::info("Batch customer score update completed", [
            'total' => $results['total'],
            'successful' => $results['successful'],
            'reclassified' => $results['reclassified'],
            'errors' => $results['errors']
        ]);

        return $results;
    }

    /**
     * Log successful score update
     */
    protected function logSuccess(CustomerScoringResultDTO $result): void
    {
        $logData = $result->toArray();

        if ($result->wasReclassified) {
            Log::info("Customer {$result->customer->id} score updated and reclassified", $logData);
        } else {
            Log::debug("Customer {$result->customer->id} score updated (no reclassification)", $logData);
        }
    }

    /**
     * Log error during score update
     */
    protected function logError(CustomerScoringResultDTO $result, array $context = []): void
    {
        Log::error("Failed to update customer {$result->customer->id} score", array_merge([
            'customer_id' => $result->customer->id,
            'error' => $result->error?->getMessage(),
            'trace' => $result->error?->getTraceAsString()
        ], $context));
    }
}
