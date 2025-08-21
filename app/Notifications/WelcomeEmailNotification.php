<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WelcomeEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->onQueue('emails');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            return (new MailMessage)
                ->subject('Welcome to Our Platform!')
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Welcome to our platform! We\'re excited to have you on board.')
                ->line('Your account has been successfully created and you can now start exploring our features.')
                ->action('Get Started', url('/dashboard'))
                ->line('Here are some things you can do:')
                ->line('• Complete your profile')
                ->line('• Browse our products and services')
                ->line('• Connect with our community')
                ->line('If you have any questions, feel free to contact our support team.')
                ->line('Thank you for joining us!')
                ->salutation('Best regards, The Team');
        } catch (\Exception $e) {
            Log::error('Error creating welcome email', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
            ]);
            
            // Return a simple fallback email
            return (new MailMessage)
                ->subject('Welcome!')
                ->line('Welcome to our platform!')
                ->action('Get Started', url('/dashboard'));
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome_email',
            'user_id' => $notifiable->id,
            'user_name' => $notifiable->name,
            'user_email' => $notifiable->email,
            'sent_at' => now()->toISOString(),
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Welcome email notification failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'failed_at' => now(),
        ]);
    }
}
