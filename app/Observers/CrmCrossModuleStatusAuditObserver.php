<?php

namespace App\Observers;

use App\Models\Delivery;
use App\Models\Installation;
use App\Services\WorkspaceAuditTrail;
use Illuminate\Database\Eloquent\Model;

/**
 * Audits CRM status transitions made through Eloquent save/update.
 * Uses the same database transaction as the source model save.
 * Invoice changes use the query builder, so they are recorded separately
 * inside InvoiceController's existing locked transaction.
 */
final class CrmCrossModuleStatusAuditObserver
{
    public function updated(Model $model): void
    {
        if (!$model->wasChanged('status')) {
            return;
        }

        if ($model instanceof Delivery) {
            $event = 'delivery.status_changed';
            $subject = 'delivery';
        } elseif ($model instanceof Installation) {
            $event = 'installation.status_changed';
            $subject = 'installation';
        } else {
            return;
        }

        app(WorkspaceAuditTrail::class)->record(
            $event,
            $subject,
            (string) $model->getKey(),
            [
                'status_from' => (string) $model->getOriginal('status'),
                'status_to' => (string) $model->getAttribute('status'),
            ],
        );
    }
}
