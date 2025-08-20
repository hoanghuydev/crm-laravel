<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CustomerTypeService;
use App\Models\CustomerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerTypeController extends Controller
{
    protected CustomerTypeService $customerTypeService;

    public function __construct(CustomerTypeService $customerTypeService)
    {
        $this->customerTypeService = $customerTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customerTypes = CustomerType::withCount('customers')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer-types.index', compact('customerTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customer-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:customer_types,name',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'min_order_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $customerType = $this->customerTypeService->createCustomerType($request->all());
            return redirect()->route('customer-types.show', $customerType)
                ->with('success', 'Customer type created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerType $customerType)
    {
        $customerType->loadCount('customers');
        return view('customer-types.show', compact('customerType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerType $customerType)
    {
        return view('customer-types.edit', compact('customerType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerType $customerType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100|unique:customer_types,name,' . $customerType->id,
            'description' => 'nullable|string',
            'discount_percentage' => 'sometimes|numeric|min:0|max:100',
            'min_order_amount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $customerType = $this->customerTypeService->updateCustomerType($customerType->id, $request->all());
            return redirect()->route('customer-types.show', $customerType)
                ->with('success', 'Customer type updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerType $customerType)
    {
        try {
            $this->customerTypeService->deactivateCustomerType($customerType->id);
            return redirect()->route('customer-types.index')
                ->with('success', 'Customer type deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
