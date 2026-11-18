<?php

namespace App\Services;

use App\Models\Installation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fail-closed link authorization, run at send-time AND on notification Open.
 * A notification is never a new permission grant.
 */
final class CrmStatusNotificationAccess
{
    public const TARGETS = [
        'delivery' => 'deliveries',
        'installation' => 'installations',
        'invoice' => 'invoices',
    ];

    public function canOpen(User $user, string $type, string $id): bool
    {
        $page = self::TARGETS[$type] ?? null;
        if ($page === null || strtolower((string) $user->status) !== 'active') return false;
        if (!Schema::hasTable($page) || !Schema::hasColumn($page, 'created_by')) return false;
        if (!app(CrmNavigationAccess::class)->canView($user, $page)) return false;

        $resolver = app(CrmPermissionResolver::class);
        $admin = $resolver->isSuperAdmin($user);
        $uid = (string) $user->getKey();

        if ($type === 'installation') {
            $query = Installation::query()->whereKey($id);
            if (!$admin) {
                $query->where(static fn ($q) => $q->where('user_id', $uid)->orWhere('created_by', $uid));
            }
            // Respect the configured, opt-in row-level restrictions for technicians.
            if (config('crm_data_scope.installations_enabled', false)) {
                $query = app(CrmDataScope::class)->applyForPermission(
                    $query, $user, 'installations', 'crm_page.installations.view', $resolver
                );
            }
            return $query->exists();
        }

        // No portable row-scope implementation exists for Delivery or Invoice.
        // In a scoped Sales deployment, fail closed for ordinary users rather
        // than assuming creator attribution overrides customer/opportunity scope.
        if (!$admin && app(CrmReportingDataScope::class)->salesScopeEnabled()) return false;
        if (!Schema::hasTable('sales_orders') || !Schema::hasColumn('sales_orders', 'created_by')) return false;

        if ($type === 'delivery') {
            $query = DB::table('deliveries as d')
                ->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'd.sales_order_id')
                ->where('d.delivery_id', $id);
            if (!$admin) {
                $query->where(static fn ($q) => $q->where('d.created_by', $uid)
                    ->orWhere('so.created_by', $uid));
            }
            return $query->exists();
        }
        $query = DB::table('invoices as i')
            ->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'i.sales_order_id')
            ->where('i.invoice_id', $id);
        if (!$admin) {
            $query->where(static fn ($q) => $q->where('i.created_by', $uid)
                ->orWhere('so.created_by', $uid));
        }
        return $query->exists();
    }
}
