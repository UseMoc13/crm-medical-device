<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Accept precisely the loopback host addresses produced by PostgreSQL
 * inet_server_addr()::text. PostgreSQL commonly includes /32 or /128.
 *
 * Never accept an entire 127/8 block, a broader CIDR mask, a proxy IP,
 * an IPv4-mapped address, or a null/unix-socket endpoint by accident.
 */
final class CrmPgLoopbackEndpointPolicy
{
    public static function isLoopback(?string $address): bool
    {
        if ($address === null) {
            return false;
        }
        $value = trim($address);
        if ($value === '' || substr_count($value, '/') > 1) {
            return false;
        }
        $parts = explode('/', $value, 2);
        $ip = $parts[0];
        $mask = $parts[1] ?? null;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return $ip === '127.0.0.1' && ($mask === null || $mask === '32');
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return inet_pton($ip) === inet_pton('::1') && ($mask === null || $mask === '128');
        }
        return false;
    }
}
