<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\CustomerScoreUpdaterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Event listener that handles customer score recalculation after order creation
 * Follows SRP - only handles the event and delegates to specialized service
 */
class RecalculateCustomerScore implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected CustomerScoreUpdaterService $scoreUpdaterService
    ) {}

    /**
     * Handle the OrderCreated event
     */
    public function handle(OrderCreated $event): void
    {
        $result = $this->scoreUpdaterService->updateAfterOrder($event->order);

        // Log additional success details for listener context
        if ($result->isSuccessful() && $result->wasReclassified) {
            Log::info("Order-triggered reclassification completed", [
                'order_id' => $event->order->id,
                'customer_id' => $result->customer->id,
                'new_type' => $result->getNewTypeName(),
                'score_change' => $result->getScoreChange()
            ]);
        }

        // Note: Errors are already logged by the service
        // We don't re-throw to prevent order creation from failing
    }

    /**
     * Handle a job failure
     */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error("Customer score recalculation job failed completely", [
            'order_id' => $event->order->id,
            'customer_id' => $event->order->customer_id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
