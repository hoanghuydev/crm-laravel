<?php

namespace App\Services\ScoringStrategies;

use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CompositeScoringStrategy implements ScoringStrategyInterface
{
    /**
     * @var ScoringStrategyInterface[]
     */
    protected array $strategies = [];

    /**
     * Initialize with scoring strategies
     *
     * @param ScoringStrategyInterface[] $strategies
     */
    public function __construct(array $strategies = [])
    {
        $this->strategies = $strategies;
    }

    /**
     * Add a scoring strategy to the composite
     */
    public function addStrategy(ScoringStrategyInterface $strategy): self
    {
        $this->strategies[] = $strategy;
        return $this;
    }

    /**
     * Remove a scoring strategy from the composite
     */
    public function removeStrategy(string $strategyName): self
    {
        $this->strategies = array_filter(
            $this->strategies, 
            fn($strategy) => $strategy->getName() !== $strategyName
        );
        
        // Re-index array
        $this->strategies = array_values($this->strategies);
        
        return $this;
    }

    /**
     * Get all strategies in the composite
     *
     * @return ScoringStrategyInterface[]
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * Calculate composite score by combining all strategies
     */
    public function calculateScore(Customer $customer, array $data = []): float
    {
        $totalScore = 0;
        $scoreBreakdown = [];

        foreach ($this->strategies as $strategy) {
            $strategyScore = $strategy->calculateScore($customer, $data);
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

        Log::info("Customer {$customer->id} composite score calculated", [
            'total_score' => $totalScore,
            'breakdown' => $scoreBreakdown
        ]);

        return round($totalScore, 3);
    }

    /**
     * Get the weight of this composite strategy
     * Returns 1.0 since weighted calculation is done internally
     */
    public function getWeight(): float
    {
        return 1.0;
    }

    /**
     * Get the name of this composite strategy
     */
    public function getName(): string
    {
        return 'composite';
    }

    /**
     * Get individual strategy score breakdown
     */
    public function getScoreBreakdown(Customer $customer, array $data = []): array
    {
        $breakdown = [];

        foreach ($this->strategies as $strategy) {
            $strategyScore = $strategy->calculateScore($customer, $data);
            $breakdown[$strategy->getName()] = [
                'raw_score' => $strategyScore,
                'weight' => $strategy->getWeight(),
                'weighted_score' => $strategyScore * $strategy->getWeight(),
            ];
        }

        return $breakdown;
    }
}
