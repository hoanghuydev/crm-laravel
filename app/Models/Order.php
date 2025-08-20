<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'payment_method_id',
        'status',
        'subtotal',
        'customer_discount_amount',
        'discount_amount',
        'total',
        'notes',
        'shipping_address',
        'order_date',
        'shipped_date',
        'delivered_date',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'customer_discount_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'order_date' => 'datetime',
            'shipped_date' => 'datetime',
            'delivered_date' => 'datetime',
        ];
    }

    /**
     * Get the customer that owns the order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the payment method for this order
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get all order items for this order
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all order discounts for this order
     */
    public function orderDiscounts(): HasMany
    {
        return $this->hasMany(OrderDiscount::class);
    }

    /**
     * Get discounts applied to this order through many-to-many
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'order_discounts')
                    ->withPivot('discount_amount')
                    ->withTimestamps();
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', now()->toDateString())
                         ->orderBy('id', 'desc')
                         ->first();
        
        $sequence = $lastOrder ? (intval(substr($lastOrder->order_number, -3)) + 1) : 1;
        
        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Calculate total discount amount
     */
    public function getTotalDiscountAmount(): float
    {
        return $this->customer_discount_amount + $this->discount_amount;
    }
}
