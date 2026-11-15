<?php

namespace App\Services;

use App\Models\CrmTeam;
use App\Models\CrmTeamMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Team membership is organizational metadata, NOT a CRM data-scope grant.
 * Invoke validateSelection() and sync() inside a database transaction.
 */
final class CrmTeamMembershipManager
{
    /** @param list<string> $ids */
    public function validateSelection(array $ids, ?string $leaderId, string $type, ?string $branchId, string $status, ?CrmTeam $team = null): void
    {
        $ids = array_values(array_unique(array_map('strval', $ids)));
        if ($leaderId && !in_array($leaderId, $ids, true)) {
            throw ValidationException::withMessages(['leader_user_id' => 'The team leader must also be selected as a member.']);
        }
        if ($type === 'permanent' && !$branchId) {
            throw ValidationException::withMessages(['branch_id' => 'Permanent teams must have a branch.']);
        }

        $existingIds = $team ? $team->activeMemberships()->pluck('user_id')->map(fn ($id) => (string) $id)->all() : [];
        if ($status === 'archived' && array_diff($ids, $existingIds)) {
            throw ValidationException::withMessages(['member_user_ids' => 'Archived teams cannot receive new members. Reactivate the team first.']);
        }

        if (!$ids) return;
        $users = User::query()->with('roles')->whereIn('user_id', $ids)->get()->keyBy('user_id');
        if ($users->count() !== count($ids)) {
            throw ValidationException::withMessages(['member_user_ids' => 'One or more selected users no longer exist.']);
        }
        $resolver = app(CrmPermissionResolver::class);
        foreach ($ids as $id) {
            /** @var User $user */
            $user = $users->get($id);
            if ($resolver->isSuperAdmin($user)) {
                throw ValidationException::withMessages(['member_user_ids' => 'Protected Super Admin accounts cannot be operational team members.']);
            }
            // Keep existing members when their status later changes; reject new inactive members.
            if (strtolower((string) $user->status) !== 'active' && !in_array($id, $existingIds, true)) {
                throw ValidationException::withMessages(['member_user_ids' => 'Only active users can be added as new team members.']);
            }
            if ($leaderId === $id && strtolower((string) $user->status) !== 'active') {
                throw ValidationException::withMessages(['leader_user_id' => 'Choose an active user as Team Leader.']);
            }
            if ($type === 'permanent' && (string) ($user->branch_id ?? '') !== (string) $branchId) {
                throw ValidationException::withMessages(['member_user_ids' => 'Permanent team members must belong to the selected branch.']);
            }
        }
    }

    /** Preserve historical memberships when users leave. Rejoining reactivates their row. */
    public function sync(CrmTeam $team, array $ids): void
    {
        $ids = array_values(array_unique(array_map('strval', $ids)));
        $existing = CrmTeamMember::query()->where('team_id', $team->getKey())
            ->lockForUpdate()->get()->keyBy('user_id');
        foreach ($existing as $userId => $member) {
            if ($member->left_at === null && !in_array((string) $userId, $ids, true)) {
                $member->update(['left_at' => now()]);
            }
        }
        foreach ($ids as $id) {
            $member = $existing->get($id);
            if ($member) {
                if ($member->left_at !== null) {
                    $member->update(['left_at' => null, 'joined_at' => now()]);
                }
            } else {
                CrmTeamMember::create([
                    'team_id' => (string) $team->getKey(), 'user_id' => $id,
                    'member_role' => 'member', 'joined_at' => now(), 'left_at' => null,
                ]);
            }
        }
    }
}
