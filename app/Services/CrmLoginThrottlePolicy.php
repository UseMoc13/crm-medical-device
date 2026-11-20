<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * A7.7.2: two independent keys for failed CRM sign-ins.
 * The identity key is intentionally independent of the request IP address,
 * so changing client addresses cannot reset the account-level attempt limit.
 * Key material is hashed; no plaintext email or address is stored in a key.
 * This class is pure: it does not read cache, sessions or the database.
 */
final class CrmLoginThrottlePolicy
{
    public const PAIR_LIMIT = 5;
    public const PAIR_DECAY_SECONDS = 60;

    // A moderate secondary ceiling limits distributed guesses against one email.
    // Failed attempts can temporarily affect legitimate users of that address.
    public const IDENTITY_LIMIT = 20;
    public const IDENTITY_DECAY_SECONDS = 300;

    /** @return array{pair:string,identity:string} */
    public static function keys(string $email, string $clientIp): array
    {
        $normalized = strtolower(trim($email));
        if ($normalized === '') {
            throw new InvalidArgumentException('Login identity is required.');
        }
        $clientIp = trim($clientIp);
        if ($clientIp === '') {
            $clientIp = 'unknown';
        }

        return [
            'pair' => 'crm-login:' . hash('sha256', $normalized . '|' . $clientIp),
            'identity' => 'crm-login-identity:' . hash('sha256', $normalized),
        ];
    }
}
