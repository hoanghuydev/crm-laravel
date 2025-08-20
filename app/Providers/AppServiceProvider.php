<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

// Events & Listeners
use App\Events\OrderCreated;
use App\Listeners\RecalculateCustomerScore;

// Contracts
use App\Contracts\CustomerTypeRepositoryInterface;
use App\Contracts\CustomerRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Contracts\DiscountRepositoryInterface;
use App\Contracts\PaymentMethodRepositoryInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Contracts\OrderItemRepositoryInterface;
use App\Contracts\OrderDiscountRepositoryInterface;
use App\Contracts\CacheServiceInterface;

// Repositories
use App\Repositories\CustomerTypeRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\ProductRepository;
use App\Repositories\DiscountRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderDiscountRepository;

// Cache Services
use App\Services\Cache\CacheManager;
use App\Services\Cache\CacheFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Repository bindings
        $this->app->bind(CustomerTypeRepositoryInterface::class, CustomerTypeRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderItemRepositoryInterface::class, OrderItemRepository::class);
        $this->app->bind(OrderDiscountRepositoryInterface::class, OrderDiscountRepository::class);

        // Register Cache Services
        $this->app->bind(CacheServiceInterface::class, function ($app) {
            $driver = config('cache.custom_driver', 'redis');
            return CacheFactory::make($driver);
        });

        $this->app->singleton('cache.manager', function ($app) {
            return new CacheManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(OrderCreated::class, RecalculateCustomerScore::class);
    }
}
