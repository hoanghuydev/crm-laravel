<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Notifications\LoginAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserLogin implements ShouldQueue
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
    public function handle(UserLoggedIn $event): void
    {
        try {
            $user = $event->user;

            // Log the login event
            Log::info('User login event processed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $event->ipAddress,
                'user_agent' => $event->userAgent,
                'login_time' => $event->loginTime->format('Y-m-d H:i:s'),
            ]);

            // Send login alert notification if needed (optional)
            // You can add conditions here to determine when to send notifications
            // For example: only for admin users, or suspicious login attempts
            if ($this->shouldSendLoginAlert($user, $event)) {
                $user->notify(new LoginAlertNotification($event));

                Log::info('Login alert notification sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_address' => $event->ipAddress,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process user login event', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw here as login alerting is not critical
        }
    }

    /**
     * Determine if login alert should be sent.
     */
    private function shouldSendLoginAlert($user, UserLoggedIn $event): bool
    {
        // Send alert for admin users
        if ($user->isAdmin()) {
            return true;
        }

        // Send alert if login from different IP (basic check)
        $lastLoginIp = cache()->get("user_{$user->id}_last_login_ip");
        if ($lastLoginIp && $lastLoginIp !== $event->ipAddress) {
            return true;
        }

        // Store current IP for next comparison
        cache()->put("user_{$user->id}_last_login_ip", $event->ipAddress, now()->addDays(30));

        return false;
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserLoggedIn $event, \Throwable $exception): void
    {
        Log::error('Login logging job failed', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'error' => $exception->getMessage(),
            'failed_at' => now(),
        ]);
    }
}
