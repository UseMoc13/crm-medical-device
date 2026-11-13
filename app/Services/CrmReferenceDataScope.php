<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * A6.11.6: opt-in reference authorization for dropdowns and submitted UUIDs.
 *
 * IMPORTANT: This is not a global scope. Existing Controller methods must call
 * visible() for dropdown queries and assertVisible() for incoming references.
 * An ordinary `exists` validation rule alone does not establish row access.
 */
final class CrmReferenceDataScope
{
    private const MODELS = [
        'customers' => Customer::class,
        'leads' => Lead::class,
        'opportunities' => Opportunity::class,
    ];

    public function enabled(string $subject): bool
    {
        $this->modelFor($subject);

        return (bool) config('crm_data_scope.' . $subject . '_enabled', false);
    }

    /** Apply the same active row-scope as the referenced module's own Index. */
    public function visible(string $subject, ?Builder $query = null): Builder
    {
        $model = $this->modelFor($subject);
        $query ??= $model::query();

        if (! $this->enabled($subject)) {
            return $query;
        }

        if (in_array($subject, ['leads', 'opportunities'], true)) {
            return app(CrmSalesDataScope::class)->visible($query, $subject);
        }

        $actor = Auth::user();
        abort_unless($actor instanceof User, 401);

        return app(CrmDataScope::class)->applyForPermission(
            $query,
            $actor,
            'customers',
            'crm_page.customers.view',
            app(CrmPermissionResolver::class)
        );
    }

    /**
     * Assert that the submitted reference is visible to the current actor.
     * An empty optional reference is accepted only if explicitly nullable.
     * For user-facing forms, preserve validation UX by returning a field error.
     */
    public function assertVisible(
        string $subject,
        ?string $id,
        bool $nullable = false,
        ?string $field = null
    ): void {
        $this->modelFor($subject);
        $field ??= match ($subject) {
            'customers' => 'customer_id',
            'leads' => 'lead_id',
            'opportunities' => 'opportunity_id',
        };

        if ($id === null || trim($id) === '') {
            if ($nullable) {
                return;
            }
            throw ValidationException::withMessages([$field => 'Please select a valid record.']);
        }

        // Existing routes with the pilot turned off retain their old behavior;
        // validation in each Controller remains responsible for record existence.
        if (! $this->enabled($subject)) {
            return;
        }

        if (! $this->visible($subject)->whereKey($id)->exists()) {
            throw ValidationException::withMessages([
                $field => 'The selected record is not available to your account.',
            ]);
        }
    }

    /** URL-based access should not disclose whether an inaccessible UUID exists. */
    public function assertUrlVisible(string $subject, string $id): void
    {
        if (! $this->enabled($subject)) {
            return;
        }

        abort_unless($this->visible($subject)->whereKey($id)->exists(), 404);
    }

    /**
     * Prevent combining a lead with a customer that does not belong to it.
     * Call this after checking the visibility of BOTH references.
     */
    public function assertLeadCustomerMatch(?string $leadId, ?string $customerId): void
    {
        if (! $leadId || ! $customerId) {
            return;
        }

        if (! Lead::query()->whereKey($leadId)->where('customer_id', $customerId)->exists()) {
            throw ValidationException::withMessages([
                'lead_id' => 'The selected lead does not belong to the selected customer.',
            ]);
        }
    }

    /** @return class-string<Customer|Lead|Opportunity> */
    private function modelFor(string $subject): string
    {
        return self::MODELS[$subject]
            ?? throw new InvalidArgumentException('Unsupported CRM reference: ' . $subject);
    }
}
