<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CustomerTypeService;
use App\Services\CustomerScoringService;
use App\Models\CustomerType;
use App\Http\Requests\CustomerTypeStoreRequest;
use App\Http\Requests\CustomerTypeUpdateRequest;
use Illuminate\Http\Request;

class CustomerTypeController extends Controller
{
    protected CustomerTypeService $customerTypeService;
    protected CustomerScoringService $customerScoringService;

    public function __construct(
        CustomerTypeService $customerTypeService,
        CustomerScoringService $customerScoringService
    ) {
        $this->customerTypeService = $customerTypeService;
        $this->customerScoringService = $customerScoringService;
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
            $validatedData = $request->validated();
            
            // Debug: Log what we're trying to update
            \Log::info('Updating CustomerType', [
                'id' => $customerType->id,
                'data' => $validatedData
            ]);
            
            $updatedCustomerType = $this->customerTypeService->updateCustomerType($customerType->id, $validatedData);
            return redirect()->route('customer-types.show', $updatedCustomerType)
                ->with('success', 'Customer type updated successfully.');
        } catch (\Exception $e) {
            \Log::error('CustomerType update failed', [
                'id' => $customerType->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Update failed: ' . $e->getMessage())
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

    /**
     * Recalculate all customer scores based on current customer types
     */
    public function recalculateScores(Request $request)
    {
        try {
            $results = $this->customerScoringService->reclassifyAllCustomers();
            
            $message = "Recalculation completed! {$results['reclassified']} out of {$results['total']} customers were reclassified.";
            
            if ($results['errors'] > 0) {
                $message .= " {$results['errors']} errors occurred.";
            }
            
            return redirect()->route('customer-types.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to recalculate scores: ' . $e->getMessage());
        }
    }

    /**
     * Get default scoring weights for AJAX requests
     */
    public function getDefaultWeights()
    {
        $defaultType = new CustomerType();
        return response()->json($defaultType->getDefaultScoringWeights());
    }

    /**
     * Recalculate customer scores for cron job
     * Returns JSON response suitable for API/cron usage
     */
    public function cronRecalculateScores()
    {
        try {
            \Log::info('Cron job: Starting customer score recalculation');
            
            $startTime = microtime(true);
            $results = $this->customerScoringService->reclassifyAllCustomers();
            $endTime = microtime(true);
            
            $executionTime = round($endTime - $startTime, 2);
            
            $response = [
                'success' => true,
                'message' => 'Customer scores recalculated successfully',
                'data' => [
                    'total_customers' => $results['total'],
                    'reclassified_customers' => $results['reclassified'],
                    'errors' => $results['errors'],
                    'execution_time_seconds' => $executionTime,
                    'processed_at' => now()->toISOString(),
                ],
            ];
            
            \Log::info('Cron job: Customer score recalculation completed', $response['data']);
            
            return response()->json($response, 200);
            
        } catch (\Exception $e) {
            \Log::error('Cron job: Customer score recalculation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to recalculate customer scores',
                'error' => $e->getMessage(),
                'processed_at' => now()->toISOString(),
            ], 500);
        }
    }
}
