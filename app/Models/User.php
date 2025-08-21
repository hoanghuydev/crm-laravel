<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Scopes\ActiveUserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'last_logout_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'last_logout_at' => 'datetime',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * The "booted" method of the model.
     * Apply global scope to automatically filter active users
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveUserScope);
    }

    /**
     * Local scope to filter active users
     * Usage: User::active()->get()
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Local scope to filter inactive users
     * Usage: User::inactive()->get()
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Local scope to include inactive users (override global scope)
     * Usage: User::withInactive()->get()
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveUserScope::class);
    }

    /**
     * Local scope to get only inactive users (override global scope)
     * Usage: User::onlyInactive()->get()
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveUserScope::class)->where('is_active', false);
    }
}
