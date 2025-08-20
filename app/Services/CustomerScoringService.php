<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Services\ScoringStrategies\ScoringStrategyInterface;
use App\Services\ScoringStrategies\TotalValueScoringStrategy;
use App\Services\ScoringStrategies\OrderCountScoringStrategy;
use App\Services\ScoringStrategies\OrderFrequencyScoringStrategy;
use App\Services\ScoringStrategies\LocationScoringStrategy;
use Illuminate\Support\Facades\Log;

class CustomerScoringService
{
    /**
     * @var ScoringStrategyInterface[]
     */
    protected array $strategies;

    /**
     * Initialize with all scoring strategies
     */
    public function __construct()
    {
        $this->strategies = [
            new TotalValueScoringStrategy(),
            new OrderCountScoringStrategy(),
            new OrderFrequencyScoringStrategy(),
            new LocationScoringStrategy(),
        ];
    }

    /**
     * Calculate and update customer score
     */
    public function calculateCustomerScore(Customer $customer): float
    {
        // Get normalization data for context
        $normalizationData = $this->getNormalizationData();
        
        $totalScore = 0;
        $scoreBreakdown = [];

        foreach ($this->strategies as $strategy) {
            $strategyScore = $strategy->calculateScore($customer, $normalizationData);
            $weightedScore = $strategyScore * $strategy->getWeight();
            $totalScore += $weightedScore;
            
            $scoreBreakdown[$strategy->getName()] = $strategyScore;
            
            Log::debug("Scoring customer {$customer->id}", [
                'strategy' => $strategy->getName(),
                'raw_score' => $strategyScore,
                'weight' => $strategy->getWeight(),
                'weighted_score' => $weightedScore
            ]);
        }

        // Update individual scores
        $customer->update([
            'total_value_score' => $scoreBreakdown['total_value'] ?? 0,
            'order_count_score' => $scoreBreakdown['order_count'] ?? 0,
            'order_frequency_score' => $scoreBreakdown['order_frequency'] ?? 0,
            'location_score' => $scoreBreakdown['location'] ?? 0,
            'current_score' => round($totalScore, 3),
            'last_score_calculated_at' => now(),
        ]);

        Log::info("Customer {$customer->id} score calculated", [
            'total_score' => $totalScore,
            'breakdown' => $scoreBreakdown
        ]);

        return round($totalScore, 3);
    }

    /**
     * Determine appropriate customer type based on score
     */
    public function determineCustomerType(Customer $customer): ?CustomerType
    {
        $score = $customer->current_score;

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
     * Batch reclassify all customers
     */
    public function reclassifyAllCustomers(): array
    {
        $customers = Customer::active()->get();
        $results = [
            'total' => $customers->count(),
            'reclassified' => 0,
            'errors' => 0,
        ];

        foreach ($customers as $customer) {
            try {
                if ($this->reclassifyCustomer($customer)) {
                    $results['reclassified']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Failed to reclassify customer {$customer->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Batch reclassification completed", $results);

        return $results;
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
        $breakdown = [];

        foreach ($this->strategies as $strategy) {
            $strategyScore = $strategy->calculateScore($customer, $normalizationData);
            $breakdown[$strategy->getName()] = [
                'raw_score' => $strategyScore,
                'weight' => $strategy->getWeight(),
                'weighted_score' => $strategyScore * $strategy->getWeight(),
            ];
        }

        $totalScore = array_sum(array_column($breakdown, 'weighted_score'));

        return [
            'customer_id' => $customer->id,
            'total_score' => round($totalScore, 3),
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
}
