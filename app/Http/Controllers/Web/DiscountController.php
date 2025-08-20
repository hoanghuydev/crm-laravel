<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountStoreRequest;
use App\Http\Requests\DiscountUpdateRequest;
use App\Services\DiscountService;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiscountController extends Controller
{
    protected DiscountService $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Display a listing of discounts
     */
    public function index(Request $request): View
    {
        $query = Discount::query();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by code or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $discounts = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('discounts.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new discount
     */
    public function create(): View
    {
        $categories = [
            'product' => 'Sản phẩm',
            'payment' => 'Thanh toán',
            'customer' => 'Khách hàng',
            'seasonal' => 'Theo mùa',
            'promotion' => 'Khuyến mại'
        ];

        $types = [
            'percentage' => 'Phần trăm (%)',
            'fixed_amount' => 'Số tiền cố định'
        ];

        return view('discounts.create', compact('categories', 'types'));
    }

    /**
     * Store a newly created discount
     */
    public function store(DiscountStoreRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['used_count'] = 0; // Initialize usage count
            
            $discount = $this->discountService->createDiscount($data);

            return redirect()->route('discounts.index')
                           ->with('success', 'Discount đã được tạo thành công.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified discount
     */
    public function show(Discount $discount): View
    {
        // Get stacking compatibility info
        $stackableCategories = Discount::getStackableCategories();
        $compatibleCategories = $stackableCategories[$discount->discount_category] ?? [];

        // Calculate some usage statistics
        $usagePercentage = $discount->usage_limit ? 
            round(($discount->used_count / $discount->usage_limit) * 100, 2) : 0;

        return view('discounts.show', compact('discount', 'compatibleCategories', 'usagePercentage'));
    }

    /**
     * Show the form for editing the specified discount
     */
    public function edit(Discount $discount): View
    {
        $categories = [
            'product' => 'Sản phẩm',
            'payment' => 'Thanh toán',
            'customer' => 'Khách hàng',
            'seasonal' => 'Theo mùa',
            'promotion' => 'Khuyến mại'
        ];

        $types = [
            'percentage' => 'Phần trăm (%)',
            'fixed_amount' => 'Số tiền cố định'
        ];

        return view('discounts.edit', compact('discount', 'categories', 'types'));
    }

    /**
     * Update the specified discount
     */
    public function update(DiscountUpdateRequest $request, Discount $discount): RedirectResponse
    {
        try {
            $data = $request->validated();
            
            $this->discountService->updateDiscount($discount->id, $data);

            return redirect()->route('discounts.index')
                           ->with('success', 'Discount đã được cập nhật thành công.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified discount
     */
    public function destroy(Discount $discount): RedirectResponse
    {
        try {
            // Check if discount is being used in any orders
            if ($discount->orderDiscounts()->count() > 0) {
                return redirect()->back()
                               ->with('error', 'Không thể xóa discount đang được sử dụng trong đơn hàng.');
            }

            $discount->delete();

            return redirect()->route('discounts.index')
                           ->with('success', 'Discount đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Có lỗi xảy ra khi xóa discount: ' . $e->getMessage());
        }
    }

    /**
     * Toggle discount status
     */
    public function toggleStatus(Discount $discount): RedirectResponse
    {
        try {
            $discount->update(['is_active' => !$discount->is_active]);
            
            $status = $discount->is_active ? 'kích hoạt' : 'vô hiệu hóa';
            
            return redirect()->back()
                           ->with('success', "Discount đã được {$status} thành công.");
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
