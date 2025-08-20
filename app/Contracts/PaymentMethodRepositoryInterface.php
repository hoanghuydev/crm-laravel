<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface PaymentMethodRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active payment methods
     */
    public function getActivePaymentMethods(): Collection;
}
