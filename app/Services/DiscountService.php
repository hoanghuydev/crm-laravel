<?php

namespace App\Services;

use App\Contracts\DiscountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DiscountService
{
    protected DiscountRepositoryInterface $discountRepository;

    public function __construct(DiscountRepositoryInterface $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    /**
     * Get all active discounts
     */
    public function getAllActiveDiscounts(): Collection
    {
        return $this->discountRepository->getActiveDiscounts();
    }

    /**
     * Get valid discounts
     */
    public function getValidDiscounts(): Collection
    {
        return $this->discountRepository->getValidDiscounts();
    }

    /**
     * Create new discount
     */
    public function createDiscount(array $data): Model
    {
        // Check code uniqueness
        if ($this->discountRepository->findByCode($data['code'])) {
            throw new \Exception('Discount code already exists');
        }

        // Validate dates
        if ($data['start_date'] >= $data['end_date']) {
            throw new \Exception('End date must be after start date');
        }

        return $this->discountRepository->create($data);
    }

    /**
     * Update discount
     */
    public function updateDiscount(int $id, array $data): Model
    {
        $discount = $this->discountRepository->findOrFail($id);

        // Check code uniqueness if being updated
        if (isset($data['code']) && $data['code'] !== $discount->code) {
            if ($this->discountRepository->findByCode($data['code'])) {
                throw new \Exception('Discount code already exists');
            }
        }

        // Validate dates if being updated
        $startDate = $data['start_date'] ?? $discount->start_date;
        $endDate = $data['end_date'] ?? $discount->end_date;
        
        if ($startDate >= $endDate) {
            throw new \Exception('End date must be after start date');
        }

        return $this->discountRepository->update($discount, $data);
    }

    /**
     * Validate discount for order
     */
    public function validateDiscountForOrder(string $code, float $orderAmount): array
    {
        $discount = $this->discountRepository->findByCode($code);
        
        if (!$discount) {
            return ['valid' => false, 'message' => 'Discount code not found'];
        }

        if (!$discount->isValidForOrder($orderAmount)) {
            return ['valid' => false, 'message' => 'Discount code is not valid for this order'];
        }

        $discountAmount = $discount->calculateDiscountAmount($orderAmount);
        
        return [
            'valid' => true,
            'discount' => $discount,
            'amount' => $discountAmount,
            'message' => 'Discount code is valid'
        ];
    }

    /**
     * Get applicable discounts for order amount
     */
    public function getApplicableDiscounts(float $orderAmount): Collection
    {
        return $this->discountRepository->getApplicableDiscounts($orderAmount);
    }

    /**
     * Get stackable discounts
     */
    public function getStackableDiscounts(): Collection
    {
        return $this->discountRepository->getStackableDiscounts();
    }

    /**
     * Calculate total discount for multiple codes with stacking logic
     */
    public function calculateTotalDiscount(array $codes, float $orderAmount): array
    {
        $validDiscounts = [];
        $errors = [];
        
        // First, validate all discount codes
        foreach ($codes as $code) {
            $validation = $this->validateDiscountForOrder($code, $orderAmount);
            
            if ($validation['valid']) {
                $validDiscounts[] = $validation['discount'];
            } else {
                $errors[] = "Code {$code}: " . $validation['message'];
            }
        }
        
        // Apply stacking algorithm
        $stackingResult = $this->calculateStackableDiscounts($validDiscounts, $orderAmount);
        
        return [
            'total_discount' => $stackingResult['total_discount'],
            'applied_discounts' => $stackingResult['applied_discounts'],
            'stacking_conflicts' => $stackingResult['conflicts'],
            'errors' => $errors
        ];
    }

    /**
     * Advanced stacking algorithm for discount calculation
     */
    public function calculateStackableDiscounts(array $discounts, float $orderAmount): array
    {
        if (empty($discounts)) {
            return [
                'total_discount' => 0,
                'applied_discounts' => [],
                'conflicts' => []
            ];
        }

        $appliedDiscounts = [];
        $conflicts = [];
        $totalDiscount = 0;
        $remainingAmount = $orderAmount;

        // Group discounts by category
        $discountsByCategory = [];
        foreach ($discounts as $discount) {
            $category = $discount->discount_category;
            if (!isset($discountsByCategory[$category])) {
                $discountsByCategory[$category] = [];
            }
            $discountsByCategory[$category][] = $discount;
        }

        // Sort each category by discount value (highest first) to maximize savings
        foreach ($discountsByCategory as $category => $categoryDiscounts) {
            usort($categoryDiscounts, function ($a, $b) use ($orderAmount) {
                $amountA = $a->calculateDiscountAmount($orderAmount);
                $amountB = $b->calculateDiscountAmount($orderAmount);
                return $amountB <=> $amountA; // Descending order
            });
            $discountsByCategory[$category] = $categoryDiscounts;
        }

        // Get stackable categories configuration
        $stackableCategories = \App\Models\Discount::getStackableCategories();

        // Apply discounts using greedy algorithm with stacking rules
        $processedCategories = [];
        
        foreach ($discountsByCategory as $category => $categoryDiscounts) {
            if (in_array($category, $processedCategories)) {
                continue;
            }

            // Apply the best discount from this category
            $primaryDiscount = $categoryDiscounts[0];
            if ($primaryDiscount->isValidForOrder($remainingAmount)) {
                $discountAmount = $primaryDiscount->calculateDiscountAmount($remainingAmount);
                
                $appliedDiscounts[] = [
                    'discount' => $primaryDiscount,
                    'amount' => $discountAmount,
                    'category' => $category
                ];
                
                $totalDiscount += $discountAmount;
                $remainingAmount -= $discountAmount;
                $processedCategories[] = $category;

                // Look for stackable discounts from other categories
                if (isset($stackableCategories[$category])) {
                    foreach ($stackableCategories[$category] as $stackableCategory) {
                        if (isset($discountsByCategory[$stackableCategory]) && 
                            !in_array($stackableCategory, $processedCategories)) {
                            
                            $stackableDiscount = $discountsByCategory[$stackableCategory][0];
                            
                            if ($stackableDiscount->isValidForOrder($remainingAmount) && 
                                $primaryDiscount->canStackWith($stackableDiscount)) {
                                
                                $stackableAmount = $stackableDiscount->calculateDiscountAmount($remainingAmount);
                                
                                $appliedDiscounts[] = [
                                    'discount' => $stackableDiscount,
                                    'amount' => $stackableAmount,
                                    'category' => $stackableCategory,
                                    'stacked_with' => $category
                                ];
                                
                                $totalDiscount += $stackableAmount;
                                $remainingAmount -= $stackableAmount;
                                $processedCategories[] = $stackableCategory;
                            }
                        }
                    }
                }
            }

            // Record conflicts (other discounts in same category that couldn't be applied)
            for ($i = 1; $i < count($categoryDiscounts); $i++) {
                $conflicts[] = [
                    'discount' => $categoryDiscounts[$i],
                    'reason' => "Cannot stack with another discount from the same category: {$category}"
                ];
            }
        }

        // Record conflicts for non-stackable categories
        foreach ($discountsByCategory as $category => $categoryDiscounts) {
            if (!in_array($category, $processedCategories)) {
                foreach ($categoryDiscounts as $discount) {
                    $conflicts[] = [
                        'discount' => $discount,
                        'reason' => "Category {$category} cannot stack with already applied categories"
                    ];
                }
            }
        }

        return [
            'total_discount' => min($totalDiscount, $orderAmount), // Cannot discount more than order amount
            'applied_discounts' => $appliedDiscounts,
            'conflicts' => $conflicts
        ];
    }

    /**
     * Get optimal discount combination for an order
     */
    public function getOptimalDiscountCombination(float $orderAmount, array $availableDiscountCodes = null): array
    {
        // If no specific codes provided, get all valid discounts
        if ($availableDiscountCodes === null) {
            $availableDiscounts = $this->getValidDiscounts()->all();
        } else {
            $availableDiscounts = [];
            foreach ($availableDiscountCodes as $code) {
                $discount = $this->discountRepository->findByCode($code);
                if ($discount && $discount->isValidForOrder($orderAmount)) {
                    $availableDiscounts[] = $discount;
                }
            }
        }

        return $this->calculateStackableDiscounts($availableDiscounts, $orderAmount);
    }
}
