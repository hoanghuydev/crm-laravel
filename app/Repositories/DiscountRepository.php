<?php

namespace App\Repositories;

use App\Contracts\DiscountRepositoryInterface;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Collection;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInterface
{
    public function __construct(Discount $model)
    {
        parent::__construct($model);
    }

    public function getActiveDiscounts(): Collection
    {
        return $this->model->active()->get();
    }

    public function getValidDiscounts(): Collection
    {
        return $this->model->valid()->get();
    }

    public function findByCode(string $code): ?object
    {
        return $this->model->where('code', $code)->first();
    }

    public function getStackableDiscounts(): Collection
    {
        return $this->model->where('can_stack', true)
                           ->valid()
                           ->get();
    }

    public function getApplicableDiscounts(float $orderAmount): Collection
    {
        return $this->model->valid()
                           ->where('min_order_amount', '<=', $orderAmount)
                           ->where(function ($query) {
                               $query->whereNull('usage_limit')
                                     ->orWhereRaw('used_count < usage_limit');
                           })
                           ->get();
    }

    public function incrementUsage(int $discountId): bool
    {
        return $this->model->where('id', $discountId)
                           ->increment('used_count') > 0;
    }
}
