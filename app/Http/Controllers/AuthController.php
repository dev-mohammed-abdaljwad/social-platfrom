<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\Auth\LoginRequest;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $result = $this->authService->loginWeb(
            $validated['email'],
            $validated['password'],
            $request->boolean('remember')
        );

        if (!$result['success']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']], 422);
            }

            return back()->withErrors([
                'email' => $result['message'],
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return redirect()->intended('/')->with('success', 'Welcome back!');
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(RegisterRequest $request)
    {
        $this->authService->registerWeb($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Registration successful']);
        }

        return redirect('/')->with('success', 'Account created successfully!');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $this->authService->logoutWeb();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out.');
    }
}