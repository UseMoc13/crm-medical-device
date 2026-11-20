<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Defensive source regression for A7.7.2. Pure test; no DB. */
final class CrmLoginThrottleSourceTest extends TestCase
{
    private function controllerSource(): string
    {
        $source = (string) file_get_contents(__DIR__.'/../../app/Http/Controllers/Auth/CrmLoginController.php');
        self::assertNotSame('', $source);
        return $source;
    }

    public function test_both_limits_are_checked_before_auth_attempt(): void
    {
        $s = $this->controllerSource();
        self::assertStringContainsString('CrmLoginThrottlePolicy::keys(', $s);
        self::assertStringContainsString('RateLimiter::tooManyAttempts($pairKey,', $s);
        self::assertStringContainsString('RateLimiter::tooManyAttempts($identityKey,', $s);
        self::assertLessThan(strpos($s, "Auth::guard('web')->attempt("), strpos($s, 'if ($pairBlocked || $identityBlocked)'));
    }

    public function test_both_limits_increment_on_invalid_password_and_inactive_account(): void
    {
        $s = $this->controllerSource();
        self::assertSame(2, substr_count($s, 'RateLimiter::hit($pairKey,'));
        self::assertSame(2, substr_count($s, 'RateLimiter::hit($identityKey,'));
        self::assertStringContainsString("!== 'active'", $s);
        self::assertStringContainsString('Auth::guard(\'web\')->logout();', $s);
    }

    public function test_success_clears_both_and_regenerates_session(): void
    {
        $s = $this->controllerSource();
        self::assertStringContainsString('RateLimiter::clear($pairKey);', $s);
        self::assertStringContainsString('RateLimiter::clear($identityKey);', $s);
        self::assertStringContainsString('$request->session()->regenerate();', $s);
    }

    public function test_logout_invalidates_session_and_rotates_csrf_token(): void
    {
        $s = $this->controllerSource();
        self::assertStringContainsString('public function destroy(Request $request)', $s);
        self::assertStringContainsString('$request->session()->invalidate();', $s);
        self::assertStringContainsString('$request->session()->regenerateToken();', $s);
    }
}
