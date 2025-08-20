<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CustomerTypeService;
use App\Models\CustomerType;
use App\Http\Requests\CustomerTypeStoreRequest;
use App\Http\Requests\CustomerTypeUpdateRequest;
use Illuminate\Http\Request;

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
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
        ];

        $customerTypes = $this->customerTypeService->getAllCustomerTypes($filters, 15);

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
    public function store(CustomerTypeStoreRequest $request)
    {
        try {
            $customerType = $this->customerTypeService->createCustomerType($request->validated());
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
        try {
            $customerType = $this->customerTypeService->getCustomerType($customerType->id);
            return view('customer-types.show', compact('customerType'));
        } catch (\Exception $e) {
            return redirect()->route('customer-types.index')
                ->with('error', $e->getMessage());
        }
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
    public function update(CustomerTypeUpdateRequest $request, CustomerType $customerType)
    {
        try {
            $updatedCustomerType = $this->customerTypeService->updateCustomerType($customerType->id, $request->validated());
            return redirect()->route('customer-types.show', $updatedCustomerType)
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
