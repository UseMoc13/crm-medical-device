<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Sends status-only generic notices to direct stakeholders, never branches.
 * Call from within the same DB transaction as the business status transition.
 */
final class CrmStatusNotificationPublisher
{
    public function changed(string $type, string $id, ?string $actorId = null): void
    {
        if (!isset(CrmStatusNotificationAccess::TARGETS[$type])) return;
        // Explicit opt-in: no migration / missing preference column = no sends.
        if (!Schema::hasTable('workspace_notification_preferences')
            || !Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')
            || !Schema::hasTable('workspace_notifications')) return;
        $sourceTable = CrmStatusNotificationAccess::TARGETS[$type];
        if (!Schema::hasTable($sourceTable)
            || !Schema::hasColumn($sourceTable, 'created_by')) return;
        if (in_array($type, ['delivery', 'invoice'], true)
            && (!Schema::hasTable('sales_orders') || !Schema::hasColumn('sales_orders', 'created_by'))) return;

        $stakeholders = match ($type) {
            'delivery' => DB::table('deliveries as d')
                ->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'd.sales_order_id')
                ->where('d.delivery_id', $id)
                ->select('d.created_by', 'so.created_by as order_creator')->first(),
            'installation' => DB::table('installations')->where('installation_id', $id)
                ->select('created_by', 'user_id as order_creator')->first(),
            'invoice' => DB::table('invoices as i')
                ->leftJoin('sales_orders as so', 'so.sales_order_id', '=', 'i.sales_order_id')
                ->where('i.invoice_id', $id)
                ->select('i.created_by', 'so.created_by as order_creator')->first(),
        };
        if (!$stakeholders) return;

        $candidates = array_unique(array_filter([
            $stakeholders->created_by ?? null,
            $stakeholders->order_creator ?? null,
        ], static fn ($value) => is_string($value) && Str::isUuid($value)));
        $center = app(WorkspaceNotificationCenter::class);
        $access = app(CrmStatusNotificationAccess::class);
        $title = match ($type) {
            'delivery' => 'Delivery status updated',
            'installation' => 'Installation status updated',
            'invoice' => 'Invoice status updated',
        };
        foreach (array_slice(array_values($candidates), 0, 2) as $userId) {
            if ($userId === $actorId) continue;
            $recipient = User::query()->whereKey($userId)->where('status', 'active')->first();
            if (!$recipient || !$access->canOpen($recipient, $type, $id)) continue;
            if (!app(WorkspaceNotificationPreferences::class)->allows($userId, 'crm_status', $type)) continue;

            // Random per-transition key: the calling transition happens once
            // after row locking; DB unique constraint protects accidental reuse.
            $center->send(
                $userId, 'crm_status', $type, $id, $title,
                'A CRM record you are responsible for has changed status.',
                'crm-status:'.(string) Str::uuid(), $actorId
            );
        }
    }
}
