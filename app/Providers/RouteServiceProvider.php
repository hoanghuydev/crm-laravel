<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        parent::boot();
        
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Login rate limiting - 5 attempts per minute by email + IP
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email', '');
            $ip = $request->ip();
            
            return [
                // Limit by email + IP combination (for targeted attacks)
                Limit::perMinute(5)->by($email . '|' . $ip)->response(function () {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again in 1 minute.',
                        'retry_after' => 60
                    ], 429);
                }),
                
                // Global limit by IP (for broader protection)
                Limit::perMinute(10)->by($ip)->response(function () {
                    return response()->json([
                        'message' => 'Too many requests from this IP. Please try again later.',
                        'retry_after' => 60
                    ], 429);
                }),
            ];
        });

        // Registration rate limiting - 3 attempts per minute by IP
        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip())->response(function () {
                    return response()->json([
                        'message' => 'Too many registration attempts. Please try again in 1 minute.',
                        'retry_after' => 60
                    ], 429);
                }),
                
                // Daily limit for registration from same IP
                Limit::perDay(10)->by($request->ip())->response(function () {
                    return response()->json([
                        'message' => 'Daily registration limit reached. Please try again tomorrow.',
                        'retry_after' => 86400
                    ], 429);
                }),
            ];
        });

        // Password reset rate limiting
        RateLimiter::for('password-reset', function (Request $request) {
            return [
                Limit::perMinute(2)->by($request->ip()),
                Limit::perHour(5)->by($request->input('email', '')),
            ];
        });

        // General API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiting for sensitive operations
        RateLimiter::for('sensitive', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(10)->by($request->user()?->id ?: $request->ip()),
            ];
        });
    }
}
