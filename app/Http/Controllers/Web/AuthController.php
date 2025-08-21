<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $credentials = $request->validated();
            $user = $this->authService->attemptLogin($credentials);

            if ($user) {
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->with('error', 'An error occurred during login. Please try again.');
        }
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $userData = $request->validated();
            $user = $this->authService->registerUser($userData);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('dashboard')
                ->with('success', 'Registration successful! Welcome to our platform, ' . $user->name . '!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            $this->authService->logoutUser($user);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', 'You have been logged out successfully.');
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'An error occurred during logout.');
        }
    }

    /**
     * Show dashboard (temporary route for testing).
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        return view('auth.dashboard', compact('user'));
    }
}
