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
        'discount_category',
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
     * Get stackable discount categories configuration
     */
    public static function getStackableCategories(): array
    {
        return [
            'product' => ['payment', 'customer'], // Product discounts can stack with payment and customer discounts
            'payment' => ['product', 'seasonal'], // Payment discounts can stack with product and seasonal discounts
            'customer' => ['product', 'promotion'], // Customer discounts can stack with product and promotion discounts
            'seasonal' => ['payment', 'promotion'], // Seasonal discounts can stack with payment and promotion discounts
            'promotion' => ['customer', 'seasonal'], // Promotion discounts can stack with customer and seasonal discounts
        ];
    }

    /**
     * Check if this discount can stack with another discount
     */
    public function canStackWith(Discount $otherDiscount): bool
    {
        // Can't stack with itself
        if ($this->id === $otherDiscount->id) {
            return false;
        }

        // Both discounts must allow stacking
        if (!$this->can_stack || !$otherDiscount->can_stack) {
            return false;
        }

        // Check if categories are stackable
        $stackableCategories = self::getStackableCategories();
        
        return isset($stackableCategories[$this->discount_category]) &&
               in_array($otherDiscount->discount_category, $stackableCategories[$this->discount_category]);
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
     * Scope to get discounts by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('discount_category', $category);
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
