<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Support\Facades\Hash;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // Set default values before creating
        if (empty($user->role)) {
            $user->role = 'customer';
        }

        if (!isset($user->is_active)) {
            $user->is_active = true;
        }

        if (!isset($user->password)) {
            // Hash password with bcrypt
            $user->password = Hash::make($user->password);
        }

        Log::info('User is being created', [
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            Log::info('New user created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ]);

            // Send welcome email notification
            $user->notify(new WelcomeEmailNotification());

            Log::info('Welcome email notification queued for user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in UserObserver created event', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        $originalUser = $user->getOriginal();

        // Log significant changes
        if ($user->isDirty('email')) {
            Log::info('User email is being changed', [
                'user_id' => $user->id,
                'old_email' => $originalUser['email'],
                'new_email' => $user->email,
            ]);
        }

        if ($user->isDirty('role')) {
            Log::info('User role is being changed', [
                'user_id' => $user->id,
                'old_role' => $originalUser['role'],
                'new_role' => $user->role,
            ]);
        }

        if ($user->isDirty('is_active')) {
            Log::info('User active status is being changed', [
                'user_id' => $user->id,
                'old_status' => $originalUser['is_active'] ? 'active' : 'inactive',
                'new_status' => $user->is_active ? 'active' : 'inactive',
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();

        if (!empty($changes)) {
            Log::info('User updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'changes' => array_keys($changes),
                'updated_at' => $user->updated_at,
            ]);
        }

        // Handle specific update scenarios
        if (isset($changes['is_active'])) {
            $this->handleAccountStatusChange($user, $changes['is_active']);
        }

        if (isset($changes['password'])) {
            Log::info('User password changed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'changed_at' => now(),
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::warning('User deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'deleted_at' => now(),
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('User restored', [
            'user_id' => $user->id,
            'email' => $user->email,
            'restored_at' => now(),
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        Log::critical('User force deleted permanently', [
            'user_id' => $user->id,
            'email' => $user->email,
            'force_deleted_at' => now(),
        ]);
    }

    /**
     * Handle account status changes.
     */
    private function handleAccountStatusChange(User $user, bool $newStatus): void
    {
        try {
            if ($newStatus) {
                Log::info('User account activated', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'activated_at' => now(),
                ]);
            } else {
                Log::warning('User account deactivated', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'deactivated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error handling account status change', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
