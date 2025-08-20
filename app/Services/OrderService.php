<?php

namespace App\Services;

use App\Contracts\OrderRepositoryInterface;
use App\Contracts\OrderItemRepositoryInterface;
use App\Contracts\OrderDiscountRepositoryInterface;
use App\Contracts\CustomerRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Contracts\DiscountRepositoryInterface;
use App\Contracts\PaymentMethodRepositoryInterface;
use App\Services\DiscountService;
use App\Services\PricingStrategyFactory;
use App\Events\OrderCreated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class OrderService
{
    protected OrderRepositoryInterface $orderRepository;
    protected OrderItemRepositoryInterface $orderItemRepository;
    protected OrderDiscountRepositoryInterface $orderDiscountRepository;
    protected CustomerRepositoryInterface $customerRepository;
    protected ProductRepositoryInterface $productRepository;
    protected DiscountRepositoryInterface $discountRepository;
    protected PaymentMethodRepositoryInterface $paymentMethodRepository;
    protected DiscountService $discountService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderDiscountRepositoryInterface $orderDiscountRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        DiscountRepositoryInterface $discountRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        DiscountService $discountService
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderDiscountRepository = $orderDiscountRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->discountRepository = $discountRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->discountService = $discountService;
    }

    /**
     * Create new order
     */
    public function createOrder(array $orderData, array $items, array $discountCodes = []): Model
    {
        return DB::transaction(function () use ($orderData, $items, $discountCodes) {
            // Validate customer
            $customer = $this->customerRepository->findOrFail($orderData['customer_id']);
            
            // Validate payment method
            $paymentMethod = $this->paymentMethodRepository->findOrFail($orderData['payment_method_id']);
            
            // Calculate subtotal and validate items
            $subtotal = 0;
            $validatedItems = [];
            
            foreach ($items as $item) {
                $product = $this->productRepository->findOrFail($item['product_id']);
                
                if (!$product->isAvailable() || $product->quantity_in_stock < $item['quantity']) {
                    throw new \Exception("Product {$product->name} is not available in requested quantity");
                }
                
                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;
                
                $validatedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                ];
            }

            // Calculate customer discount using pricing strategy
            $pricingStrategy = PricingStrategyFactory::create($customer);
            $customerDiscountAmount = $pricingStrategy->calculateDiscount($subtotal, $customer);

            // Calculate discount codes using stacking algorithm
            $discountResult = $this->discountService->calculateTotalDiscount($discountCodes, $subtotal);
            
            if (!empty($discountResult['errors'])) {
                throw new \Exception('Discount validation failed: ' . implode(', ', $discountResult['errors']));
            }

            $discountAmount = $discountResult['total_discount'];
            $appliedDiscounts = $discountResult['applied_discounts'] ?? [];

            // Calculate total
            $total = $subtotal - $customerDiscountAmount - $discountAmount;

            // Create order
            $order = $this->orderRepository->create([
                'order_number' => $this->orderRepository->generateOrderNumber(),
                'customer_id' => $orderData['customer_id'],
                'payment_method_id' => $orderData['payment_method_id'],
                'status' => 'pending',
                'subtotal' => $subtotal,
                'customer_discount_amount' => $customerDiscountAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'notes' => $orderData['notes'] ?? null,
                'shipping_address' => $orderData['shipping_address'] ?? null,
                'order_date' => now(),
            ]);

            // Create order items
            foreach ($validatedItems as $item) {
                $item['order_id'] = $order->id;
                $this->orderItemRepository->create($item);
                
                // Reduce product stock
                $product = $this->productRepository->findOrFail($item['product_id']);
                $product->reduceStock($item['quantity']);
            }

            // Create order discounts using stacking results
            foreach ($appliedDiscounts as $appliedDiscount) {
                $this->orderDiscountRepository->create([
                    'order_id' => $order->id,
                    'discount_id' => $appliedDiscount['discount']->id,
                    'discount_amount' => $appliedDiscount['amount'],
                ]);
                
                // Increment discount usage
                $appliedDiscount['discount']->incrementUsage();
            }

            // Fire OrderCreated event for customer score recalculation
            Event::dispatch(new OrderCreated($order));

            return $order->load(['customer', 'paymentMethod', 'orderItems.product', 'orderDiscounts.discount']);
        });
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, string $status): Model
    {
        $order = $this->orderRepository->findOrFail($orderId);
        
        $updateData = ['status' => $status];
        
        if ($status === 'shipped') {
            $updateData['shipped_date'] = now();
        } elseif ($status === 'delivered') {
            $updateData['delivered_date'] = now();
        }
        
        return $this->orderRepository->update($order, $updateData);
    }

    /**
     * Cancel order
     */
    public function cancelOrder(int $orderId): Model
    {
        return DB::transaction(function () use ($orderId) {
            $order = $this->orderRepository->findOrFail($orderId);
            
            if (!$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled');
            }

            // Restore product stock
            $orderItems = $this->orderItemRepository->getByOrder($orderId);
            foreach ($orderItems as $item) {
                $product = $this->productRepository->findOrFail($item->product_id);
                $newStock = $product->quantity_in_stock + $item->quantity;
                $this->productRepository->updateStock($item->product_id, $newStock);
            }

            // Restore discount usage counts
            $orderDiscounts = $this->orderDiscountRepository->getByOrder($orderId);
            foreach ($orderDiscounts as $orderDiscount) {
                $discount = $this->discountRepository->findOrFail($orderDiscount->discount_id);
                $discount->decrement('used_count');
            }

            return $this->orderRepository->update($order, ['status' => 'cancelled']);
        });
    }

    /**
     * Get order details
     */
    public function getOrderDetails(int $orderId): Model
    {
        return $this->orderRepository->findOrFail($orderId)
                    ->load(['customer', 'paymentMethod', 'orderItems.product', 'orderDiscounts.discount']);
    }

    /**
     * Get customer orders
     */
    public function getCustomerOrders(int $customerId): Collection
    {
        return $this->orderRepository->getByCustomer($customerId);
    }

    /**
     * Get orders by status
     */
    public function getOrdersByStatus(string $status): Collection
    {
        return $this->orderRepository->getByStatus($status);
    }

    /**
     * Get orders with filtering and pagination
     */
    public function getFilteredOrders(array $filters = [], int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->orderRepository->getFilteredOrders($filters, $perPage);
    }

    /**
     * Get order with detailed relationships
     */
    public function getOrderWithDetails(int $orderId): Model
    {
        return $this->orderRepository->findOrFail($orderId)
                    ->load([
                        'customer.customerType', 
                        'paymentMethod', 
                        'orderItems.product', 
                        'orderDiscounts.discount'
                    ]);
    }

    /**
     * Preview order calculation with discount stacking
     */
    public function previewOrderCalculation(array $items, array $discountCodes, int $customerId): array
    {
        // Validate customer
        $customer = $this->customerRepository->findOrFail($customerId);
        
        // Calculate subtotal
        $subtotal = 0;
        $itemsDetails = [];
        
        foreach ($items as $item) {
            $product = $this->productRepository->findOrFail($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];
            $subtotal += $itemTotal;
            
            $itemsDetails[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total_price' => $itemTotal,
            ];
        }
        
        // Calculate customer discount using pricing strategy
        $pricingStrategy = PricingStrategyFactory::create($customer);
        $customerDiscountAmount = $pricingStrategy->calculateDiscount($subtotal, $customer);
        $customerDiscountPercentage = $customer->getDiscountPercentage();
        
        // Calculate discount codes using stacking algorithm
        $discountResult = $this->discountService->calculateTotalDiscount($discountCodes, $subtotal);
        $discountAmount = $discountResult['total_discount'];
        
        // Calculate total
        $total = $subtotal - $customerDiscountAmount - $discountAmount;
        
        return [
            'items' => $itemsDetails,
            'subtotal' => $subtotal,
            'customer_discount_percentage' => $customerDiscountPercentage,
            'customer_discount_amount' => $customerDiscountAmount,
            'discount_stacking_result' => $discountResult,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'customer' => $customer,
        ];
    }

    /**
     * Calculate revenue for date range
     */
    public function calculateRevenue(string $startDate, string $endDate): float
    {
        return $this->orderRepository->getTotalRevenue($startDate, $endDate);
    }
}
