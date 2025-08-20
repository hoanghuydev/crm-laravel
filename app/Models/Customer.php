<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_type_id',
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'current_score',
        'total_value_score',
        'order_frequency_score',
        'order_count_score',
        'location_score',
        'last_score_calculated_at',
        'last_order_at',
        'joined_at',
        'is_active',
        'email_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'current_score' => 'decimal:3',
            'total_value_score' => 'decimal:3',
            'order_frequency_score' => 'decimal:3',
            'order_count_score' => 'decimal:3',
            'location_score' => 'decimal:3',
            'last_score_calculated_at' => 'datetime',
            'last_order_at' => 'datetime',
            'joined_at' => 'datetime',
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the customer type that owns the customer
     */
    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    /**
     * Get all orders for this customer
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope to get only active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get customer's applicable discount percentage
     */
    public function getDiscountPercentage(): float
    {
        return $this->customerType?->discount_percentage ?? 0;
    }

    /**
     * Check if customer is from HCM (Ho Chi Minh City)
     */
    public function isFromHCM(): bool
    {
        return $this->address && (
            str_contains(strtolower($this->address), 'hcm') ||
            str_contains(strtolower($this->address), 'ho chi minh') ||
            str_contains(strtolower($this->address), 'sài gòn') ||
            str_contains(strtolower($this->address), 'saigon')
        );
    }

    /**
     * Get total spent amount (calculated from orders)
     */
    public function getTotalSpent(): float
    {
        return $this->orders()->sum('total') ?? 0;
    }

    /**
     * Get total order count (calculated from orders)
     */
    public function getTotalOrderCount(): int
    {
        return $this->orders()->count();
    }

    /**
     * Get average days between orders
     */
    public function getAverageDaysBetweenOrders(): float
    {
        $orders = $this->orders()
            ->orderBy('created_at')
            ->pluck('created_at')
            ->toArray();

        if (count($orders) < 2) {
            return 0; // Not enough data
        }

        $totalDays = 0;
        for ($i = 1; $i < count($orders); $i++) {
            $totalDays += $orders[$i]->diffInDays($orders[$i - 1]);
        }

        return $totalDays / (count($orders) - 1);
    }

    /**
     * Get days since joining
     */
    public function getDaysSinceJoining(): int
    {
        return $this->joined_at ? now()->diffInDays($this->joined_at) : 0;
    }

    /**
     * Check if score needs recalculation
     */
    public function needsScoreRecalculation(): bool
    {
        if (!$this->last_score_calculated_at) {
            return true;
        }

        // Recalculate if last calculation was more than 1 hour ago
        return now()->diffInHours($this->last_score_calculated_at) > 1;
    }

    /**
     * Get score breakdown for debugging
     */
    public function getScoreBreakdown(): array
    {
        return [
            'total_value_score' => $this->total_value_score,
            'order_count_score' => $this->order_count_score,
            'order_frequency_score' => $this->order_frequency_score,
            'location_score' => $this->location_score,
            'current_score' => $this->current_score,
            'last_calculated' => $this->last_score_calculated_at,
        ];
    }
}
