<?php

namespace App\DTOs;

use App\Models\Customer;
use App\Models\CustomerType;

/**
 * Data Transfer Object for customer scoring operation results
 */
readonly class CustomerScoringResultDTO
{
    public function __construct(
        public Customer $customer,
        public float $oldScore,
        public float $newScore,
        public ?CustomerType $oldType,
        public CustomerType $newType,
        public bool $wasReclassified,
        public array $scoreBreakdown = [],
        public ?\Throwable $error = null
    ) {}

    /**
     * Create successful result
     */
    public static function success(
        Customer $customer,
        float $oldScore,
        float $newScore,
        ?CustomerType $oldType,
        CustomerType $newType,
        bool $wasReclassified,
        array $scoreBreakdown = []
    ): self {
        return new self(
            customer: $customer,
            oldScore: $oldScore,
            newScore: $newScore,
            oldType: $oldType,
            newType: $newType,
            wasReclassified: $wasReclassified,
            scoreBreakdown: $scoreBreakdown
        );
    }

    /**
     * Create error result
     */
    public static function error(Customer $customer, \Throwable $error): self
    {
        return new self(
            customer: $customer,
            oldScore: $customer->current_score ?? 0,
            newScore: $customer->current_score ?? 0,
            oldType: $customer->customerType,
            newType: $customer->customerType ?? new CustomerType(),
            wasReclassified: false,
            error: $error
        );
    }

    /**
     * Check if operation was successful
     */
    public function isSuccessful(): bool
    {
        return $this->error === null;
    }

    /**
     * Get formatted score change
     */
    public function getScoreChange(): float
    {
        return round($this->newScore - $this->oldScore, 3);
    }

    /**
     * Get old type name
     */
    public function getOldTypeName(): string
    {
        return $this->oldType?->name ?? 'Unknown';
    }

    /**
     * Get new type name
     */
    public function getNewTypeName(): string
    {
        return $this->newType->name;
    }

    /**
     * Convert to array for logging
     */
    public function toArray(): array
    {
        return [
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'old_score' => $this->oldScore,
            'new_score' => $this->newScore,
            'score_change' => $this->getScoreChange(),
            'old_type' => $this->getOldTypeName(),
            'new_type' => $this->getNewTypeName(),
            'was_reclassified' => $this->wasReclassified,
            'successful' => $this->isSuccessful(),
            'error' => $this->error?->getMessage(),
        ];
    }
}
