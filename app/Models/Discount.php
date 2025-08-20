<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Discount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'can_stack',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'can_stack' => 'boolean',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all order discounts
     */
    public function orderDiscounts(): HasMany
    {
        return $this->hasMany(OrderDiscount::class);
    }

    /**
     * Scope to get only active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get currently valid discounts
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
                     ->where('start_date', '<=', $now)
                     ->where('end_date', '>=', $now);
    }

    /**
     * Check if discount is valid for order
     */
    public function isValidForOrder(float $orderAmount): bool
    {
        $now = Carbon::now();
        
        return $this->is_active
               && $this->start_date <= $now
               && $this->end_date >= $now
               && $orderAmount >= $this->min_order_amount
               && ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }

    /**
     * Calculate discount amount for order
     */
    public function calculateDiscountAmount(float $orderAmount): float
    {
        if (!$this->isValidForOrder($orderAmount)) {
            return 0;
        }

        $discountAmount = 0;

        if ($this->type === 'percentage') {
            $discountAmount = $orderAmount * ($this->value / 100);
        } else {
            $discountAmount = $this->value;
        }

        // Apply maximum discount limit if set
        if ($this->max_discount_amount !== null) {
            $discountAmount = min($discountAmount, $this->max_discount_amount);
        }

        return $discountAmount;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
