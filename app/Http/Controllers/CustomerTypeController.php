<?php

namespace App\Http\Controllers;

use App\Services\CustomerTypeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
    public function index(): JsonResponse
    {
        try {
            $customerTypes = $this->customerTypeService->getAllActiveCustomerTypes();
            return response()->json([
                'success' => true,
                'data' => $customerTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:customer_types,name',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'min_order_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customerType = $this->customerTypeService->createCustomerType($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Customer type created successfully',
                'data' => $customerType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $customerType = $this->customerTypeService->getCustomerType($id);
            return response()->json([
                'success' => true,
                'data' => $customerType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100|unique:customer_types,name,' . $id,
            'description' => 'nullable|string',
            'discount_percentage' => 'sometimes|numeric|min:0|max:100',
            'min_order_amount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customerType = $this->customerTypeService->updateCustomerType($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Customer type updated successfully',
                'data' => $customerType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->customerTypeService->deactivateCustomerType($id);
            return response()->json([
                'success' => true,
                'message' => 'Customer type deactivated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Activate customer type
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $customerType = $this->customerTypeService->activateCustomerType($id);
            return response()->json([
                'success' => true,
                'message' => 'Customer type activated successfully',
                'data' => $customerType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Permanently delete customer type
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->customerTypeService->deleteCustomerType($id);
            return response()->json([
                'success' => true,
                'message' => 'Customer type deleted permanently'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
