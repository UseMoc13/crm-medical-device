<?php

namespace App\Services;

use App\Models\ServiceTicket;
use App\Models\User;

/**
 * A7.9.8: A work log must have a known, authenticated, active author.
 * The assigned active technician writes operational logs; a protected Super Admin
 * may write an explicitly attributed administrative entry for UAT/intervention.
 * This does not grant CRUD or data-scope permission; the route guard remains.
 */
final class CrmWorkLogAuthorPolicy
{
    public static function permits(
        bool $active,
        bool $ticketAssigned,
        bool $sameAssignee,
        bool $technicianEligible,
        bool $superAdmin,
        string $ticketStatus
    ): bool {
        return $active
            && $ticketAssigned
            && in_array($ticketStatus, ['in_progress', 'on_hold'], true)
            && ($superAdmin || ($sameAssignee && $technicianEligible));
    }

    public function canWrite(?User $actor, ServiceTicket $ticket): bool
    {
        if ($actor === null) return false;
        $active = strtolower(trim((string) $actor->status)) === 'active';
        $ticketAssigned = !empty($ticket->user_id);
        $sameAssignee = $ticketAssigned && (string) $actor->getKey() === (string) $ticket->user_id;
        if (!$active || !$ticketAssigned || !in_array((string) $ticket->status, ['in_progress', 'on_hold'], true)) {
            return false;
        }
        $admin = app(CrmPermissionResolver::class)->isSuperAdmin($actor);
        $technicianEligible = $sameAssignee && !$admin
            && app(CrmUserAssignmentEligibility::class)->eligibleQuery('technician')
                ->whereKey($actor->getKey())->exists();
        return self::permits($active, $ticketAssigned, $sameAssignee,
            $technicianEligible, $admin, (string) $ticket->status);
    }
}
