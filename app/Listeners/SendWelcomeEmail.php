<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        try {
            $user = $event->user;

            // Send welcome email notification
            $user->notify(new WelcomeEmailNotification());

            Log::info('Welcome email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to handle retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::critical('Welcome email job failed permanently', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'error' => $exception->getMessage(),
            'failed_at' => now(),
        ]);
    }
}
