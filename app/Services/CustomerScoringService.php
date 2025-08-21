<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Services\ScoringStrategies\CompositeScoringStrategy;
use App\Services\ScoringStrategies\TotalValueScoringStrategy;
use App\Services\ScoringStrategies\OrderCountScoringStrategy;
use App\Services\ScoringStrategies\OrderFrequencyScoringStrategy;
use App\Services\ScoringStrategies\LocationScoringStrategy;
use Illuminate\Support\Facades\Log;

class CustomerScoringService
{
    /**
     * @var CompositeScoringStrategy
     */
    protected CompositeScoringStrategy $compositeStrategy;

    /**
     * Initialize with composite scoring strategy
     */
    public function __construct()
    {
        $this->compositeStrategy = new CompositeScoringStrategy([
            new TotalValueScoringStrategy(),
            new OrderCountScoringStrategy(),
            new OrderFrequencyScoringStrategy(),
            new LocationScoringStrategy(),
        ]);
    }

    /**
     * Calculate and update customer score
     */
    public function calculateCustomerScore(Customer $customer): float
    {
        // Get normalization data for context
        $normalizationData = $this->getNormalizationData();
        
        // Use composite strategy to calculate total score
        $totalScore = $this->compositeStrategy->calculateScore($customer, $normalizationData);
        
        // Get breakdown for individual scores
        $scoreBreakdown = $this->compositeStrategy->getScoreBreakdown($customer, $normalizationData);

        // Update individual scores
        $customer->update([
            'total_value_score' => $scoreBreakdown['total_value']['raw_score'] ?? 0,
            'order_count_score' => $scoreBreakdown['order_count']['raw_score'] ?? 0,
            'order_frequency_score' => $scoreBreakdown['order_frequency']['raw_score'] ?? 0,
            'location_score' => $scoreBreakdown['location']['raw_score'] ?? 0,
            'current_score' => $totalScore,
            'last_score_calculated_at' => now(),
        ]);

        return $totalScore;
    }

    /**
     * Determine appropriate customer type based on score
     */
    public function determineCustomerType(Customer $customer): ?CustomerType
    {
        $score = (float) $customer->current_score;

        // Get all active customer types ordered by priority (descending)
        $customerTypes = CustomerType::active()
            ->byPriority()
            ->get();

        // Find the highest priority type that the customer qualifies for
        foreach ($customerTypes as $type) {
            if ($type->qualifiesForScore($score)) {
                return $type;
            }
        }

        // Fallback to lowest priority type if no match found
        return $customerTypes->sortBy('priority')->first();
    }

    /**
     * Reclassify customer based on current score
     */
    public function reclassifyCustomer(Customer $customer): bool
    {
        $currentScore = $this->calculateCustomerScore($customer);
        $newCustomerType = $this->determineCustomerType($customer);

        if (!$newCustomerType) {
            Log::warning("No customer type found for customer {$customer->id} with score {$currentScore}");
            return false;
        }

        if ($customer->customer_type_id !== $newCustomerType->id) {
            $oldTypeName = $customer->customerType?->name ?? 'Unknown';
            $customer->update(['customer_type_id' => $newCustomerType->id]);
            
            Log::info("Customer {$customer->id} reclassified", [
                'from' => $oldTypeName,
                'to' => $newCustomerType->name,
                'score' => $currentScore
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get normalization data for scoring calculations
     */
    protected function getNormalizationData(): array
    {
        // These could be cached or configured via settings
        return [
            'max_total_spent' => 10000000,    // 10M VND for score = 1.0
            'max_order_count' => 20,          // 20 orders for score = 1.0  
            'max_avg_days' => 60,             // 60 days avg = score = 0
        ];
    }

    /**
     * Get detailed scoring breakdown for a customer
     */
    public function getCustomerScoreBreakdown(Customer $customer): array
    {
        $normalizationData = $this->getNormalizationData();
        
        // Use composite strategy to get breakdown
        $breakdown = $this->compositeStrategy->getScoreBreakdown($customer, $normalizationData);
        $totalScore = $this->compositeStrategy->calculateScore($customer, $normalizationData);

        return [
            'customer_id' => $customer->id,
            'total_score' => $totalScore,
            'breakdown' => $breakdown,
            'customer_type' => $customer->customerType?->name,
            'calculated_at' => now()->toISOString(),
        ];
    }

    /**
     * Check if customer needs score recalculation
     */
    public function shouldRecalculateScore(Customer $customer): bool
    {
        return $customer->needsScoreRecalculation();
    }

    /**
     * Get the composite scoring strategy
     */
    public function getCompositeStrategy(): CompositeScoringStrategy
    {
        return $this->compositeStrategy;
    }
}
