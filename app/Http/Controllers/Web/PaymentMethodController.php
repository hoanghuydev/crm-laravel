<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PaymentMethodService;
use App\Models\PaymentMethod;
use App\Http\Requests\PaymentMethodStoreRequest;
use App\Http\Requests\PaymentMethodUpdateRequest;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    protected PaymentMethodService $paymentMethodService;

    public function __construct(PaymentMethodService $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
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

        $paymentMethods = $this->paymentMethodService->getAllPaymentMethods($filters, 15);

        return view('payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('payment-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentMethodStoreRequest $request)
    {
        try {
            $paymentMethod = $this->paymentMethodService->createPaymentMethod($request->validated());

            return redirect()->route('payment-methods.show', $paymentMethod)
                ->with('success', 'Payment method created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod)
    {
        try {
            $paymentMethod = $this->paymentMethodService->getPaymentMethodById($paymentMethod->id);
            return view('payment-methods.show', compact('paymentMethod'));
        } catch (\Exception $e) {
            return redirect()->route('payment-methods.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return view('payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentMethodUpdateRequest $request, PaymentMethod $paymentMethod)
    {
        try {
            $updatedPaymentMethod = $this->paymentMethodService->updatePaymentMethod($paymentMethod->id, $request->validated());

            return redirect()->route('payment-methods.show', $updatedPaymentMethod)
                ->with('success', 'Payment method updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        try {
            $wasDeleted = $this->paymentMethodService->deletePaymentMethod($paymentMethod->id);
            
            if ($wasDeleted) {
                $message = 'Payment method deleted successfully.';
            } else {
                $message = 'Payment method deactivated successfully (cannot delete as it has associated orders).';
            }
            
            return redirect()->route('payment-methods.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle payment method status
     */
    public function toggleStatus(PaymentMethod $paymentMethod)
    {
        try {
            $updatedPaymentMethod = $this->paymentMethodService->togglePaymentMethodStatus($paymentMethod->id);
            
            $status = $updatedPaymentMethod->is_active ? 'activated' : 'deactivated';
            return redirect()->back()
                ->with('success', "Payment method {$status} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
