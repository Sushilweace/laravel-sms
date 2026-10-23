<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = 'login.'.strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function quickLogin(string $role): RedirectResponse
    {
        if (app()->isProduction() && ! config('app.demo_mode', false)) {
            abort(404, 'Demo quick-login is disabled in production.');
        }

        $roleMap = [
            'superadmin' => 'superadmin@school.test',
            'principal' => 'principal@school.test',
            'teacher' => 'teacher@school.test',
            'accountant' => 'accountant@school.test',
            'parent' => 'parent@school.test',
            'student' => 'student@school.test',
        ];

        $email = $roleMap[$role] ?? null;
        if (! $email) {
            return redirect()->route('login')->with('error', 'Unknown role');
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            Auth::login($user);
            $user->update(['last_login_at' => now()]);

            return redirect()->route('dashboard')->with('success', "Logged in as {$user->name} ({$role})");
        }

        return redirect()->route('login')->with('error', 'Demo user not found. Please run seeders.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
