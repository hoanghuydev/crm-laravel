<?php

namespace App\Services;

use App\Models\User;
use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthService
{
    /**
     * Attempt to login user with provided credentials.
     *
     * @param array $credentials
     * @return User|null
     * @throws \Exception
     */
    public function attemptLogin(array $credentials): ?User
    {
        try {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                Log::warning('Login attempt with non-existent email', [
                    'email' => $credentials['email'],
                    'ip' => request()->ip(),
                ]);
                return null;
            }

            if (!$user->is_active) {
                Log::warning('Login attempt with inactive user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                ]);
                throw new \Exception('Your account has been deactivated. Please contact support.');
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                Log::warning('Failed login attempt - invalid password', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                ]);
                return null;
            }

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // Fire login event
            event(new UserLoggedIn($user));

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Login process error', [
                'email' => $credentials['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            throw $e;
        }
    }

    /**
     * Register a new user.
     *
     * @param array $userData
     * @return User
     * @throws \Exception
     */
    public function registerUser(array $userData): User
    {
        try {
            // Check if email already exists
            if (User::where('email', $userData['email'])->exists()) {
                throw new \Exception('Email address is already registered.');
            }

            // Create new user
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'is_active' => true,
                'email_verified_at' => null, // Will be set when email is verified
                'created_at' => now(),
            ]);

            // Fire registration event
            event(new UserRegistered($user));

            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip' => request()->ip(),
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('User registration error', [
                'email' => $userData['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            throw $e;
        }
    }

    /**
     * Logout user and perform cleanup.
     *
     * @param User|null $user
     * @return void
     */
    public function logoutUser(?User $user): void
    {
        try {
            if ($user) {
                // Update last logout timestamp
                $user->update(['last_logout_at' => now()]);

                Log::info('User logged out', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Logout process error', [
                'user_id' => $user?->id ?? 'unknown',
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
        }
    }

    /**
     * Get user by email address.
     *
     * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User
    {
        try {
            return User::where('email', $email)->first();
        } catch (\Exception $e) {
            Log::error('Error retrieving user by email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Activate user account.
     *
     * @param int $userId
     * @return bool
     * @throws ModelNotFoundException
     */
    public function activateUser(int $userId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $user->update(['is_active' => true]);

            Log::info('User account activated', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for activation', ['user_id' => $userId]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('User activation error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Deactivate user account.
     *
     * @param int $userId
     * @return bool
     * @throws ModelNotFoundException
     */
    public function deactivateUser(int $userId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $user->update(['is_active' => false]);

            Log::info('User account deactivated', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for deactivation', ['user_id' => $userId]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('User deactivation error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Change user password.
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     * @throws ModelNotFoundException
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        try {
            $user = User::findOrFail($userId);
            $user->update(['password' => Hash::make($newPassword)]);

            Log::info('User password changed', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        } catch (ModelNotFoundException $e) {
            Log::error('User not found for password change', ['user_id' => $userId]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Password change error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
