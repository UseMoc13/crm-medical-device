<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * A6.9 preparatory, opt-in row-level scope builder.
 *
 * IMPORTANT: This class is not middleware, and does not silently change any
 * existing CRM query. Controllers must explicitly call apply() and authorize
 * the corresponding view/action permission before using scoped results.
 */
final class CrmDataScope
{
    /**
     * Tables audited to contain a user_id assignment/ownership column.
     * Semantics are module-specific: owner for Sales and assignee for Service.
     * There is intentionally NO guess for Delivery, Warranty or Invoice.
     */
    public const SUBJECTS = [
        'customers' => ['user_id' => 'owner'],
        'leads' => ['user_id' => 'owner'],
        'opportunities' => ['user_id' => 'owner'],
        'activities' => ['user_id' => 'owner'],
        'service_tickets' => ['user_id' => 'assignee'],
        'installations' => ['user_id' => 'assignee'],
        'maintenance_schedules' => ['user_id' => 'assignee'],
    ];

    /**
     * Adds a scope predicate to an existing query. Never executes a query.
     * Empty grants and unsupported scope/subject combinations fail closed.
     * Multiple role scopes are combined with OR inside a nested WHERE.
     *
     * @param EloquentBuilder|QueryBuilder $query
     * @param list<string> $scopes explicit grants resolved for one permission
     */
    public function apply(
        EloquentBuilder|QueryBuilder $query,
        User $user,
        string $table,
        array $scopes
    ): EloquentBuilder|QueryBuilder {
        if (! array_key_exists($table, self::SUBJECTS)) {
            throw new InvalidArgumentException('Unsupported CRM data-scope subject: ' . $table);
        }

        $scopes = array_values(array_unique($scopes));
        $valid = ['all', 'branch', 'assigned', 'own'];
        if (array_diff($scopes, $valid)) {
            throw new InvalidArgumentException('Unsupported/unimplemented CRM data scope (team is not implemented).');
        }
        if (in_array('all', $scopes, true)) {
            return $query;
        }

        $semantics = self::SUBJECTS[$table]['user_id'];
        $userId = (string) $user->getKey();
        $branchId = $user->branch_id;
        $column = $table . '.user_id';

        return $query->where(function ($nested) use ($scopes, $semantics, $column, $userId, $branchId) {
            // Always false initially, then OR exactly the allowed paths.
            // This prevents empty/NULL scopes from accidentally granting all.
            $nested->whereRaw('1 = 0');

            if (($semantics === 'owner' && in_array('own', $scopes, true))
                || ($semantics === 'assignee' && in_array('assigned', $scopes, true))) {
                $nested->orWhere($column, '=', $userId);
            }

            // Branch is based on the owner/assignee's branch, not a branch
            // directly stored on the CRM record. Unassigned records are NOT
            // automatically visible, and user without branch gets no branch grant.
            if (in_array('branch', $scopes, true) && $branchId !== null) {
                $nested->orWhereIn($column, DB::table('users')
                    ->select('user_id')->where('branch_id', '=', $branchId));
            }
        });
    }

    /**
     * Explicitly pair a module's view grant with the selected role scopes.
     * Calling code must separately authorize any mutation action.
     */
    public function applyForPermission(
        EloquentBuilder|QueryBuilder $query,
        User $user,
        string $table,
        string $viewPermission,
        CrmPermissionResolver $permissions
    ): EloquentBuilder|QueryBuilder {
        if (! $permissions->allows($user, $viewPermission)) {
            // A missing page permission must not grant a row just because the
            // user happens to own it.
            return $query->whereRaw('1 = 0');
        }
        return $this->apply($query, $user, $table, $permissions->scopes($user, $viewPermission));
    }
}
