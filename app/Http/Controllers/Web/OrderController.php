<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\CustomerService;
use App\Services\ProductService;
use App\Services\PaymentMethodService;
use App\Models\Order;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected CustomerService $customerService;
    protected ProductService $productService;
    protected PaymentMethodService $paymentMethodService;

    public function __construct(
        OrderService $orderService,
        CustomerService $customerService,
        ProductService $productService,
        PaymentMethodService $paymentMethodService
    ) {
        $this->orderService = $orderService;
        $this->customerService = $customerService;
        $this->productService = $productService;
        $this->paymentMethodService = $paymentMethodService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'paymentMethod', 'orderItems']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->get('customer_id'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->get('date_to'));
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(15);
        $customers = $this->customerService->getAllActiveCustomers();

        // Status options for filter
        $statusOptions = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];

        return view('orders.index', compact('orders', 'customers', 'statusOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = $this->customerService->getAllActiveCustomers();
        $products = $this->productService->getAllAvailableProducts();
        $paymentMethods = $this->paymentMethodService->getAllActivePaymentMethods();

        return view('orders.create', compact('customers', 'products', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderStoreRequest $request)
    {
        try {
            $orderData = $request->only(['customer_id', 'payment_method_id', 'notes', 'shipping_address']);
            $items = $request->input('items', []);
            $discountCodes = $request->input('discount_codes', []);

            $order = $this->orderService->createOrder($orderData, $items, $discountCodes);
            
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load([
            'customer.customerType', 
            'paymentMethod', 
            'orderItems.product', 
            'orderDiscounts.discount'
        ]);
        
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $order->load(['customer', 'paymentMethod', 'orderItems.product', 'orderDiscounts.discount']);
        $customers = $this->customerService->getAllActiveCustomers();
        $products = $this->productService->getAllAvailableProducts();
        $paymentMethods = $this->paymentMethodService->getAllActivePaymentMethods();

        // Status options
        $statusOptions = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];

        return view('orders.edit', compact('order', 'customers', 'products', 'paymentMethods', 'statusOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        try {
            // For basic updates (status, notes, shipping address)
            if ($request->has('status')) {
                $updatedOrder = $this->orderService->updateOrderStatus($order->id, $request->status);
            }
            
            // Additional update logic can be added here for other fields
            
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Order $order)
    {
        try {
            $this->orderService->cancelOrder($order->id);
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled'
        ]);

        try {
            $this->orderService->updateOrderStatus($order->id, $request->status);
            return redirect()->back()
                ->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
