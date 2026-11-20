<?php

namespace Tests\Unit;

use App\Services\CrmLoginThrottlePolicy as Policy;
use PHPUnit\Framework\TestCase;

/** Pure security policy tests. No database, session or cache access. */
final class CrmLoginThrottlePolicyTest extends TestCase
{
    public function test_same_identity_is_rate_limited_across_different_ips(): void
    {
        $a = Policy::keys('staff@example.test', '192.0.2.10');
        $b = Policy::keys('staff@example.test', '192.0.2.11');
        self::assertSame($a['identity'], $b['identity']);
        self::assertNotSame($a['pair'], $b['pair']);
    }

    public function test_email_case_and_outer_whitespace_cannot_split_identity(): void
    {
        $a = Policy::keys(' Staff@Example.Test ', '192.0.2.10');
        $b = Policy::keys('staff@example.test', '192.0.2.10');
        self::assertSame($a, $b);
    }

    public function test_different_users_have_different_identity_keys(): void
    {
        self::assertNotSame(
            Policy::keys('one@example.test', '192.0.2.10')['identity'],
            Policy::keys('two@example.test', '192.0.2.10')['identity']
        );
    }

    public function test_no_plaintext_email_or_ip_leaks_into_cache_keys(): void
    {
        $keys = Policy::keys('private.user@example.test', '192.0.2.123');
        foreach ($keys as $key) {
            self::assertStringNotContainsString('private.user', $key);
            self::assertStringNotContainsString('192.0.2.123', $key);
            self::assertMatchesRegularExpression('/^[a-z-]+:[a-f0-9]{64}$/D', $key);
        }
    }

    public function test_original_per_pair_limit_is_preserved(): void
    {
        self::assertSame(5, Policy::PAIR_LIMIT);
        self::assertSame(60, Policy::PAIR_DECAY_SECONDS);
    }

    public function test_account_limit_is_independent_and_bounded(): void
    {
        self::assertGreaterThan(Policy::PAIR_LIMIT, Policy::IDENTITY_LIMIT);
        self::assertLessThanOrEqual(30, Policy::IDENTITY_LIMIT);
        self::assertSame(300, Policy::IDENTITY_DECAY_SECONDS);
    }

    public function test_blank_ip_has_stable_unknown_fallback(): void
    {
        self::assertSame(Policy::keys('a@example.test', '')['pair'], Policy::keys('a@example.test', 'unknown')['pair']);
    }

    public function test_blank_email_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Policy::keys('  ', '192.0.2.10');
    }
}
