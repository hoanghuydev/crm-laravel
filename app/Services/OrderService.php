<?php

namespace App\Services;

use App\Contracts\OrderRepositoryInterface;
use App\Contracts\OrderItemRepositoryInterface;
use App\Contracts\OrderDiscountRepositoryInterface;
use App\Contracts\CustomerRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Contracts\DiscountRepositoryInterface;
use App\Contracts\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected OrderRepositoryInterface $orderRepository;
    protected OrderItemRepositoryInterface $orderItemRepository;
    protected OrderDiscountRepositoryInterface $orderDiscountRepository;
    protected CustomerRepositoryInterface $customerRepository;
    protected ProductRepositoryInterface $productRepository;
    protected DiscountRepositoryInterface $discountRepository;
    protected PaymentMethodRepositoryInterface $paymentMethodRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderDiscountRepositoryInterface $orderDiscountRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        DiscountRepositoryInterface $discountRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderDiscountRepository = $orderDiscountRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->discountRepository = $discountRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
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

            // Calculate customer discount
            $customerDiscountAmount = 0;
            $customerDiscountPercentage = $customer->getDiscountPercentage();
            if ($customerDiscountPercentage > 0 && $subtotal >= $customer->customerType->min_order_amount) {
                $customerDiscountAmount = $subtotal * ($customerDiscountPercentage / 100);
            }

            // Calculate discount codes
            $discountAmount = 0;
            $validDiscounts = [];
            
            foreach ($discountCodes as $code) {
                $discount = $this->discountRepository->findByCode($code);
                if (!$discount) {
                    throw new \Exception("Discount code {$code} not found");
                }
                
                if (!$discount->isValidForOrder($subtotal)) {
                    throw new \Exception("Discount code {$code} is not valid for this order");
                }
                
                $codeDiscountAmount = $discount->calculateDiscountAmount($subtotal);
                $discountAmount += $codeDiscountAmount;
                
                $validDiscounts[] = [
                    'discount' => $discount,
                    'amount' => $codeDiscountAmount,
                ];
            }

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

            // Create order discounts
            foreach ($validDiscounts as $discountData) {
                $this->orderDiscountRepository->create([
                    'order_id' => $order->id,
                    'discount_id' => $discountData['discount']->id,
                    'discount_amount' => $discountData['amount'],
                ]);
                
                // Increment discount usage
                $this->discountRepository->incrementUsage($discountData['discount']->id);
            }

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
     * Calculate revenue for date range
     */
    public function calculateRevenue(string $startDate, string $endDate): float
    {
        return $this->orderRepository->getTotalRevenue($startDate, $endDate);
    }
}
