<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A7.7.4.3 — observational assessment only; never changes publication state,
 * file visibility, storage disks, or the database.
 */
final class CrmAnnouncementExposurePolicy
{
    public static function decide(int $references, int $hidden, bool $publicStorageLinkDetected): string
    {
        if ($references < 0 || $hidden < 0 || $hidden > $references) {
            throw new \InvalidArgumentException('Invalid reference counts');
        }
        if ($hidden > 0) {
            return $publicStorageLinkDetected
                ? 'HOLD_HIDDEN_PUBLIC_DISK_REFERENCES'
                : 'REVIEW_HIDDEN_REFERENCES_PUBLIC_SERVING_NOT_VERIFIED';
        }
        if ($references > 0) {
            return 'REVIEW_PUBLIC_ASSET_VISIBILITY_POLICY';
        }
        return 'NO_CURRENT_STORED_ASSET_REFERENCES';
    }
}
