<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Audits only Eloquent model create/update events.
 * Raw Query Builder writes, mass updates, and SQL imports require separate coverage.
 */
final class CrmCreatorAttribution
{
    private const MODELS = [
        \App\Models\Brand::class,
        \App\Models\ProductCategory::class,
        \App\Models\Product::class,
        \App\Models\Warehouse::class,
        \App\Models\Customer::class,
        \App\Models\Contact::class,
        \App\Models\Lead::class,
        \App\Models\Opportunity::class,
        \App\Models\Quotation::class,
        \App\Models\SalesOrder::class,
        \App\Models\Contract::class,
        \App\Models\Delivery::class,
        \App\Models\Installation::class,
        \App\Models\Warranty::class,
        \App\Models\ServiceTicket::class,
        \App\Models\MaintenanceSchedule::class,
        \App\Models\SerialNumber::class,
        \App\Models\StockMovement::class,
    ];

    public static function register(): void
    {
        foreach (self::MODELS as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $modelClass::creating(static function (Model $model): void {
                $userId = self::authenticatedUserId();
                if ($userId === null) {
                    return;
                }

                // Force the authenticated actor, never trust request payload.
                $model->setAttribute('created_by', $userId);
                $model->setAttribute('updated_by', $userId);
            });

            $modelClass::updating(static function (Model $model): void {
                $userId = self::authenticatedUserId();
                if ($userId !== null) {
                    $model->setAttribute('updated_by', $userId);
                }
            });
        }
    }

    private static function authenticatedUserId(): ?string
    {
        $user = Auth::user();
        if ($user === null) {
            return null;
        }

        $identifier = (string) $user->getAuthIdentifier();

        return $identifier !== '' ? $identifier : null;
    }
}
