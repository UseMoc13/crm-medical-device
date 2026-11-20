<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CrmLoginThrottlePolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CrmLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.crm-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $email = Str::lower(trim($credentials['email']));
        $keys = CrmLoginThrottlePolicy::keys($email, (string) ($request->ip() ?? 'unknown'));
        $pairKey = $keys['pair'];
        $identityKey = $keys['identity'];

        // Two independent limits: per email+IP and per email across IPs.
        // A forged/rotating client IP must not reset the identity limit.
        $pairBlocked = RateLimiter::tooManyAttempts($pairKey, CrmLoginThrottlePolicy::PAIR_LIMIT);
        $identityBlocked = RateLimiter::tooManyAttempts($identityKey, CrmLoginThrottlePolicy::IDENTITY_LIMIT);
        if ($pairBlocked || $identityBlocked) {
            $seconds = max(
                1,
                $pairBlocked ? RateLimiter::availableIn($pairKey) : 0,
                $identityBlocked ? RateLimiter::availableIn($identityKey) : 0
            );
            return back()->withErrors([
                'email' => "Too many login attempts. Try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (!Auth::guard('web')->attempt(['email' => $email, 'password' => $credentials['password']])) {
            RateLimiter::hit($pairKey, CrmLoginThrottlePolicy::PAIR_DECAY_SECONDS);
            RateLimiter::hit($identityKey, CrmLoginThrottlePolicy::IDENTITY_DECAY_SECONDS);
            return back()->withErrors(['email' => 'Invalid email or password.'])
                ->onlyInput('email');
        }

        // Authentication does not bypass the CRM account-status policy.
        $user = Auth::guard('web')->user();
        if (Str::lower(trim((string) ($user->status ?? ''))) !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($pairKey, CrmLoginThrottlePolicy::PAIR_DECAY_SECONDS);
            RateLimiter::hit($identityKey, CrmLoginThrottlePolicy::IDENTITY_DECAY_SECONDS);
            return redirect()->route('login')->withErrors([
                'email' => 'This account is not active. Contact your administrator.',
            ]);
        }

        RateLimiter::clear($pairKey);
        RateLimiter::clear($identityKey);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Welcome back!');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have signed out successfully.');
    }
}
