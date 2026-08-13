<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login:' . Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            $user = Auth::user();

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditService::log('auth.login');

            // Set default property if user has one
            $firstProperty = Property::whereHas('organization', fn($q) => $q->where('id', $user->organization_id))
                ->first();

            if ($firstProperty) {
                session(['current_property_id' => $firstProperty->id]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($key, 60);

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request)
    {
        AuditService::log('auth.logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login');
    }
}
