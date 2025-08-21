<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'quantity_in_stock',
        'sku',
        'image_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity_in_stock' => 'integer',
        ];
    }

    /**
     * Get all order items for this product
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get products in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity_in_stock', '>', 0)
                     ->where('status', '!=', 'out_of_stock');
    }

    /**
     * Check if product is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->quantity_in_stock > 0;
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock(int $quantity): void
    {
        $this->quantity_in_stock = max(0, $this->quantity_in_stock - $quantity);
        if ($this->quantity_in_stock === 0) {
            $this->status = 'out_of_stock';
        }
        $this->save();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Generate a unique slug for the product
     */
    public function generateSlug(): string
    {
        $slug = Str::slug($this->name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = $product->generateSlug();
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = $product->generateSlug();
            }
        });
    }
}
