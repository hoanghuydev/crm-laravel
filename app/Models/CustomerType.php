<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
        'min_order_amount',
        'minimum_score',
        'priority',
        'scoring_weights',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'minimum_score' => 'decimal:3',
            'priority' => 'integer',
            'scoring_weights' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all customers for this customer type
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Scope to get only active customer types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by priority (higher priority first)
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Get default scoring weights
     */
    public function getDefaultScoringWeights(): array
    {
        return [
            'total_value_weight' => 0.35,      // 35% - Tổng giá trị đơn hàng
            'order_count_weight' => 0.25,      // 25% - Số lượng đơn hàng  
            'order_frequency_weight' => 0.25,  // 25% - Tần suất đặt hàng
            'location_weight' => 0.15,         // 15% - Vị trí địa lý
        ];
    }

    /**
     * Get scoring weights (use custom or default)
     */
    public function getScoringWeights(): array
    {
        return $this->scoring_weights ?? $this->getDefaultScoringWeights();
    }

    /**
     * Check if customer score qualifies for this type
     */
    public function qualifiesForScore(float $score): bool
    {
        return $score >= $this->minimum_score;
    }
}
