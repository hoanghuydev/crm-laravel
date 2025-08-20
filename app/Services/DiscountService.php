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
     * Calculate total discount for multiple codes
     */
    public function calculateTotalDiscount(array $codes, float $orderAmount): array
    {
        $totalDiscount = 0;
        $validDiscounts = [];
        $errors = [];
        
        foreach ($codes as $code) {
            $validation = $this->validateDiscountForOrder($code, $orderAmount);
            
            if ($validation['valid']) {
                $validDiscounts[] = [
                    'code' => $code,
                    'discount' => $validation['discount'],
                    'amount' => $validation['amount']
                ];
                $totalDiscount += $validation['amount'];
            } else {
                $errors[] = "Code {$code}: " . $validation['message'];
            }
        }
        
        return [
            'total_discount' => $totalDiscount,
            'valid_discounts' => $validDiscounts,
            'errors' => $errors
        ];
    }
}
