<?php

namespace App\Notifications;

use App\Events\UserLoggedIn;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class LoginAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected UserLoggedIn $loginEvent;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserLoggedIn $loginEvent)
    {
        $this->loginEvent = $loginEvent;
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            $loginTime = $this->loginEvent->loginTime->format('F j, Y \a\t g:i A');
            $ipAddress = $this->loginEvent->ipAddress;
            
            return (new MailMessage)
                ->subject('Security Alert: New Login to Your Account')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('We detected a new login to your account.')
                ->line('**Login Details:**')
                ->line('• Time: ' . $loginTime)
                ->line('• IP Address: ' . $ipAddress)
                ->line('• Device: ' . $this->getDeviceInfo())
                ->line('If this was you, you can ignore this email.')
                ->line('If you don\'t recognize this login, please secure your account immediately.')
                ->action('Secure My Account', url('/profile/security'))
                ->line('For your security, we recommend:')
                ->line('• Change your password if this wasn\'t you')
                ->line('• Enable two-factor authentication')
                ->line('• Review your recent account activity')
                ->salutation('Stay secure, The Security Team');
        } catch (\Exception $e) {
            Log::error('Error creating login alert email', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
            ]);
            
            // Return a simple fallback email
            return (new MailMessage)
                ->subject('Login Alert')
                ->line('New login detected on your account.')
                ->line('Time: ' . $this->loginEvent->loginTime->format('Y-m-d H:i:s'))
                ->line('IP: ' . $this->loginEvent->ipAddress);
        }
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'login_alert',
            'title' => 'New Login Detected',
            'message' => 'A new login was detected on your account.',
            'login_details' => [
                'ip_address' => $this->loginEvent->ipAddress,
                'user_agent' => $this->loginEvent->userAgent,
                'login_time' => $this->loginEvent->loginTime->toISOString(),
                'device_info' => $this->getDeviceInfo(),
            ],
            'action_url' => url('/profile/security'),
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Get device information from user agent.
     */
    private function getDeviceInfo(): string
    {
        $userAgent = $this->loginEvent->userAgent;
        
        // Basic device detection
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android')) {
            return 'Mobile Device';
        } elseif (str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        } elseif (str_contains($userAgent, 'Windows')) {
            return 'Windows Computer';
        } elseif (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS')) {
            return 'Mac Computer';
        } elseif (str_contains($userAgent, 'Linux')) {
            return 'Linux Computer';
        }
        
        return 'Unknown Device';
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Login alert notification failed', [
            'user_id' => $this->loginEvent->user->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'failed_at' => now(),
        ]);
    }
}
