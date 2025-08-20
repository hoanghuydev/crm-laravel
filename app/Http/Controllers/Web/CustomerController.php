<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use App\Services\CustomerTypeService;
use App\Models\Customer;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerService $customerService;
    protected CustomerTypeService $customerTypeService;

    public function __construct(
        CustomerService $customerService,
        CustomerTypeService $customerTypeService
    ) {
        $this->customerService = $customerService;
        $this->customerTypeService = $customerTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::with('customerType');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by customer type
        if ($request->filled('customer_type')) {
            $query->where('customer_type_id', $request->get('customer_type'));
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15);
        $customerTypes = $this->customerTypeService->getAllActiveCustomerTypes();

        return view('customers.index', compact('customers', 'customerTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customerTypes = $this->customerTypeService->getAllActiveCustomerTypes();
        return view('customers.create', compact('customerTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerStoreRequest $request)
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());
            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load(['customerType', 'orders' => function($query) {
            $query->orderBy('order_date', 'desc');
        }]);
        
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $customerTypes = $this->customerTypeService->getAllActiveCustomerTypes();
        return view('customers.edit', compact('customer', 'customerTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        try {
            $updatedCustomer = $this->customerService->updateCustomer($customer->id, $request->validated());
            return redirect()->route('customers.show', $updatedCustomer)
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (deactivate).
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->deactivateCustomer($customer->id);
            return redirect()->route('customers.index')
                ->with('success', 'Customer deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Activate customer
     */
    public function activate(Customer $customer)
    {
        try {
            $this->customerService->activateCustomer($customer->id);
            return redirect()->back()
                ->with('success', 'Customer activated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
