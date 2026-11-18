<?php

namespace App\Observers;

use App\Models\Delivery;
use App\Models\Installation;
use App\Services\CrmStatusNotificationPublisher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Eloquent Delivery/Installation transitions; within the saving transaction. */
final class CrmCrossModuleStatusNotificationObserver
{
    public function updated(Model $model): void
    {
        if (!$model->wasChanged('status')) return;
        $type = match (true) {
            $model instanceof Delivery => 'delivery',
            $model instanceof Installation => 'installation',
            default => null,
        };
        if ($type === null) return;
        app(CrmStatusNotificationPublisher::class)->changed(
            $type, (string) $model->getKey(),
            Auth::guard('web')->id() ? (string) Auth::guard('web')->id() : null
        );
    }
}
