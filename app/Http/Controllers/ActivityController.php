<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\CrmInstallationActivityDataScope;
use App\Services\CrmReferenceDataScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    private const TYPES = ['Call', 'Meeting', 'Follow-up', 'Demo', 'Site Visit', 'Task', 'Other'];

    private function scoped()
    {
        // Always enforce owner isolation while activity data-scope is intentionally OFF.
        // When ON, delegate the visibility rules to the established RBAC scope service.
        $scope = app(CrmInstallationActivityDataScope::class);
        $query = Activity::query();
        if ($scope->enabled('activities')) {
            return $scope->visible($query, 'activities');
        }
        return $query->where('user_id', (string) Auth::id());
    }

    private function findVisible(Activity $activity): Activity
    {
        return $this->scoped()->whereKey($activity->getKey())->firstOrFail();
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(self::TYPES)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $query = $this->scoped()->with(['user', 'customer', 'lead', 'opportunity']);
        if ($search = trim((string) ($validated['search'] ?? ''))) {
            $query->where(function ($q) use ($search) {
                $term = '%' . addcslashes($search, '%_\\') . '%';
                $q->where('subject', 'ilike', $term)
                    ->orWhere('description', 'ilike', $term);
            });
        }
        if (!empty($validated['type'])) $query->where('activity_type', $validated['type']);
        if (!empty($validated['from'])) $query->whereDate('activity_date', '>=', $validated['from']);
        if (!empty($validated['to'])) $query->whereDate('activity_date', '<=', $validated['to']);
        $activities = $query->orderByDesc('activity_date')->orderByDesc('created_at')->paginate(10)->withQueryString();
        return view('activities.index', ['activities' => $activities, 'types' => self::TYPES]);
    }

    private function referenceOptions(): array
    {
        $scope = app(CrmReferenceDataScope::class);
        return [
            'customers' => $scope->visible('customers')->orderBy('customer_name')->get(),
            'leads' => $scope->visible('leads')->orderBy('created_at', 'desc')->limit(300)->get(),
            'opportunities' => $scope->visible('opportunities')->orderBy('created_at', 'desc')->limit(300)->get(),
            'types' => self::TYPES,
        ];
    }

    public function create(): View
    {
        return view('activities.create', $this->referenceOptions());
    }

    private function validated(Request $request): array
    {
        // Browser form uses separate keyboard-editable date and time fields.
        // Keep activity_date validation for backwards-compatible API/form clients.
        if ($request->has('activity_day') || $request->has('activity_time')) {
            $schedule = $request->validate([
                'activity_day' => ['required', 'date_format:Y-m-d'],
                'activity_time' => ['required', 'date_format:H:i'],
            ]);
            $request->merge([
                'activity_date' => $schedule['activity_day'] . ' ' . $schedule['activity_time'] . ':00',
            ]);
        }

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'activity_type' => ['required', Rule::in(self::TYPES)],
            'description' => ['nullable', 'string', 'max:5000'],
            'activity_date' => ['required', 'date'],
            'customer_id' => ['nullable', 'uuid', 'exists:customers,customer_id'],
            'lead_id' => ['nullable', 'uuid', 'exists:leads,lead_id'],
            'opportunity_id' => ['nullable', 'uuid', 'exists:opportunities,opportunity_id'],
        ]);
        $scope = app(CrmReferenceDataScope::class);
        foreach (['customers' => 'customer_id', 'leads' => 'lead_id', 'opportunities' => 'opportunity_id'] as $subject => $field) {
            $scope->assertVisible($subject, $data[$field] ?? null, true, $field);
        }
        $scope->assertLeadCustomerMatch($data['lead_id'] ?? null, $data['customer_id'] ?? null);
        return $data;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        // Never trust a submitted user_id; the current authenticated user owns the activity.
        $data['user_id'] = (string) Auth::id();
        if (app(CrmInstallationActivityDataScope::class)->enabled('activities')) {
            app(CrmInstallationActivityDataScope::class)->authorizeActivityOwner($data['user_id']);
        }
        // Eloquent HasUuids generates activity_id before INSERT, so route generation
        // never relies on refreshing a PostgreSQL DEFAULT gen_random_uuid().
        $activity = Activity::create($data);
        return redirect()->route('activities.show', ['activity' => $activity->getKey()])
            ->with('success', 'Activity created successfully.');
    }

    public function show(Activity $activity): View
    {
        $activity = $this->findVisible($activity)->load(['user', 'customer', 'lead', 'opportunity']);
        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity): View
    {
        $activity = $this->findVisible($activity);
        abort_unless($activity->user_id === (string) Auth::id(), 403, 'Only the owner can edit this activity.');
        return view('activities.edit', ['activity' => $activity, ...$this->referenceOptions()]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $activity = $this->findVisible($activity);
        abort_unless($activity->user_id === (string) Auth::id(), 403, 'Only the owner can update this activity.');
        $data = $this->validated($request);
        // Re-check the record inside the transaction; lock the row before modifying it.
        DB::transaction(function () use ($activity, $data) {
            $record = $this->scoped()->whereKey($activity->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($record->user_id === (string) Auth::id(), 403);
            $record->update($data);
        });
        return redirect()->route('activities.show', ['activity' => $activity->getKey()])->with('success', 'Activity updated successfully.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity = $this->findVisible($activity);
        abort_unless($activity->user_id === (string) Auth::id(), 403, 'Only the owner can delete this activity.');
        DB::transaction(function () use ($activity) {
            $record = $this->scoped()->whereKey($activity->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($record->user_id === (string) Auth::id(), 403);
            $record->delete();
        });
        return redirect()->route('activities.index')->with('success', 'Activity deleted successfully.');
    }
}
