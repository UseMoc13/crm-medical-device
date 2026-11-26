<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CrmPgLoopbackEndpointPolicy as Policy;
use PHPUnit\Framework\TestCase;

/** Pure network address classification. No Laravel, HTTP, or database. */
final class CrmPgLoopbackEndpointPolicyTest extends TestCase
{
    public function test_postgresql_loopback_addresses_are_accepted(): void
    {
        foreach (['127.0.0.1', '127.0.0.1/32', '::1', '::1/128',
            '0:0:0:0:0:0:0:1/128'] as $value) {
            self::assertTrue(Policy::isLoopback($value), $value);
        }
    }

    public function test_non_loopback_and_broad_masks_are_rejected(): void
    {
        foreach ([null, '', 'localhost', '127.0.0.2', '127.1', '127.0.0.0/24',
            '127.0.0.1/24', '127.0.0.1/31', '127.0.0.1/032',
            '127.0.0.1/32/0', '192.168.1.100/32', '10.0.0.2',
            '::1/64', '::ffff:127.0.0.1/128', '::', '::/128',
            'example.com', '127.0.0.1evil'] as $value) {
            self::assertFalse(Policy::isLoopback($value), (string) $value);
        }
    }
}
