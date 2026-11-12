<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\SerialNumber;
use App\Models\User;
use App\Services\MaintenanceWorkflowRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    private const STATUSES = [
        'Scheduled',
        'In Progress',
        'Completed',
        'Cancelled',
    ];

    private const OPEN_STATUSES = [
        'Scheduled',
        'In Progress',
    ];

    private const TYPES = [
        'Preventive',
        'Corrective',
        'Calibration',
        'Inspection',
        'Safety Test',
    ];

    private const RESULTS = [
        'Passed',
        'Passed with Notes',
        'Requires Follow-up',
        'Failed',
    ];

    /**
     * Daftar jadwal maintenance.
     */
    public function index(Request $request): View
    {
        $query = MaintenanceSchedule::query()
            ->with([
                'serialNumber.product',
                'serialNumber.customer',
                'user',
            ])
            ->withCount('records');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('maintenance_type', 'ILIKE', "%{$search}%")
                    ->orWhere('notes', 'ILIKE', "%{$search}%")
                    ->orWhereHas('serialNumber', function ($serial) use ($search) {
                        $serial->where('serial_number', 'ILIKE', "%{$search}%")
                            ->orWhereHas('product', function ($product) use ($search) {
                                $product->where('product_name', 'ILIKE', "%{$search}%");
                            })
                            ->orWhereHas('customer', function ($customer) use ($search) {
                                $customer->where('customer_name', 'ILIKE', "%{$search}%");
                            });
                    })
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('name', 'ILIKE', "%{$search}%");
                    });
            });
        }

        // Filter status (termasuk status virtual "overdue").
        $status = $request->input('status');

        if ($status === 'overdue') {
            $query->whereIn('status', self::OPEN_STATUSES)
                ->whereDate('scheduled_date', '<', today()->toDateString());
        } elseif (in_array($status, self::STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($request->filled('type') && in_array($request->input('type'), self::TYPES, true)) {
            $query->where('maintenance_type', $request->input('type'));
        }

        $allowedSorts = [
            'scheduled_date',
            'maintenance_type',
            'status',
            'created_at',
        ];

        $sort = in_array($request->input('sort'), $allowedSorts, true)
            ? $request->input('sort')
            : 'scheduled_date';

        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $maintenances = $query
            ->orderBy($sort, $direction)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // Ringkasan.
        $counts = MaintenanceSchedule::query()
            ->select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'total' => (int) $counts->sum(),
            'scheduled' => (int) ($counts['Scheduled'] ?? 0),
            'in_progress' => (int) ($counts['In Progress'] ?? 0),
            'completed' => (int) ($counts['Completed'] ?? 0),
            'overdue' => MaintenanceSchedule::query()
                ->whereIn('status', self::OPEN_STATUSES)
                ->whereDate('scheduled_date', '<', today()->toDateString())
                ->count(),
        ];

        return view('maintenance.index', [
            'maintenances' => $maintenances,
            'statuses' => self::STATUSES,
            'types' => self::TYPES,
            'summary' => $summary,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    /**
     * Form tambah jadwal.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $followupSource = null;
        if ($request->filled('followup_from')) {
            $request->validate(['followup_from' => ['required', 'uuid']]);
            $followupSource = MaintenanceSchedule::query()
                ->with(['serialNumber.product', 'records'])
                ->findOrFail($request->input('followup_from'));

            if (! $this->requiresFollowup($followupSource)) {
                return redirect()->route('maintenance.show', $followupSource->getKey())
                    ->with('error', 'A follow-up can only be created after a Completed maintenance with the latest result Requires Follow-up or Failed.');
            }
            if ($this->followupExists($followupSource)) {
                return redirect()->route('maintenance.show', $followupSource->getKey())
                    ->with('error', 'A follow-up schedule already exists for this maintenance.');
            }
        }

        return view('maintenance.create', array_merge($this->formData(), [
            'followupSource' => $followupSource,
        ]));
    }

    /**
     * Simpan jadwal baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->scheduleRules());

        $validated['status'] = 'Scheduled';
        $sourceId = $request->input('followup_from');
        $request->validate(['followup_from' => ['nullable', 'uuid']]);

        $maintenance = DB::transaction(function () use ($validated, $sourceId): MaintenanceSchedule {
            if ($sourceId) {
                // Lock source to serialize competing follow-up creation requests.
                $source = MaintenanceSchedule::query()->whereKey($sourceId)
                    ->lockForUpdate()->firstOrFail();
                if (! $this->requiresFollowup($source)) {
                    throw ValidationException::withMessages([
                        'followup_from' => 'This source schedule is not eligible for follow-up.',
                    ]);
                }
                if ($validated['serial_number_id'] !== $source->serial_number_id) {
                    throw ValidationException::withMessages([
                        'serial_number_id' => 'A follow-up must use the same medical device as its source.',
                    ]);
                }
                if ($this->followupExists($source)) {
                    throw ValidationException::withMessages([
                        'followup_from' => 'A follow-up schedule already exists for this maintenance.',
                    ]);
                }
                // Explicit traceability without database changes.
                $marker = $this->followupMarker($source->getKey());
                $validated['notes'] = $marker . ($validated['notes'] ? "\n" . $validated['notes'] : '');
            }
            return MaintenanceSchedule::create($validated);
        }, 3);

        return redirect()
            ->route('maintenance.show', $maintenance->maintenance_schedule_id)
            ->with('success', $sourceId ? 'Follow-up maintenance schedule successfully created.' : 'Maintenance schedule successfully created.');
    }

    /**
     * Detail jadwal beserta riwayat pelaksanaan.
     */
    public function show(MaintenanceSchedule $maintenance): View
    {
        $maintenance->load([
            'serialNumber.product',
            'serialNumber.customer',
            'user',
        ]);

        // Tabel ringkas (dipaginasi) di halaman detail.
        $records = $maintenance->records()
            ->with('user')
            ->paginate(5, ['*'], 'records_page')
            ->withQueryString();

        // Seluruh record untuk modal "Show All".
        $allRecords = $maintenance->records()
            ->with('user')
            ->get();

        $canFollowup = $this->requiresFollowup($maintenance);
        $followupSchedule = $canFollowup
            ? MaintenanceSchedule::query()->where('notes', 'like', $this->followupMarker($maintenance->getKey()) . '%')
                ->orderBy('created_at')->first()
            : null;

        return view('maintenance.show', [
            'maintenance' => $maintenance,
            'records' => $records,
            'allRecords' => $allRecords,
            'results' => self::RESULTS,
            'canFollowup' => $canFollowup,
            'followupSchedule' => $followupSchedule,
        ]);
    }

    /**
     * Form edit jadwal.
     */
    public function edit(MaintenanceSchedule $maintenance): View|RedirectResponse
    {
        // Keep the Edit action visible in the list, but reject finalized schedules
        // before rendering the edit form (server-side protection as well as UI).
        if (in_array($maintenance->status, ['Completed', 'Cancelled'], true)) {
            return redirect()->route('maintenance.index')
                ->with('error', 'This maintenance schedule is ' . $maintenance->status . ' and cannot be edited.');
        }

        $maintenance->load(['serialNumber.product', 'serialNumber.customer', 'user']);

        return view(
            'maintenance.edit',
            array_merge(['maintenance' => $maintenance], $this->formData())
        );
    }

    /**
     * Perbarui jadwal.
     */
    public function update(
        Request $request,
        MaintenanceSchedule $maintenance
    ): RedirectResponse {
        $validated = $request->validate($this->scheduleRules());

        DB::transaction(function () use ($maintenance, $validated): void {
            $schedule = MaintenanceSchedule::query()->whereKey($maintenance->getKey())
                ->lockForUpdate()->firstOrFail();
            $recordCount = MaintenanceRecord::query()
                ->where('maintenance_schedule_id', $schedule->getKey())->count();
            if (in_array($schedule->status, ['Completed', 'Cancelled'], true)) {
                throw ValidationException::withMessages(['status' => 'Finalized maintenance schedules are read-only.']);
            }
            // Editing schedule details cannot transition status. Use dedicated workflow instead.
            if ($validated['status'] !== $schedule->status) {
                throw ValidationException::withMessages(['status' => 'Change maintenance status using the workflow buttons on the detail page.']);
            }
            if ($recordCount > 0 && (
                $validated['serial_number_id'] !== $schedule->serial_number_id ||
                $validated['maintenance_type'] !== $schedule->maintenance_type
            )) {
                throw ValidationException::withMessages(['serial_number_id' => 'Device and maintenance type cannot be changed after records exist.']);
            }
            // Preserve the source marker on follow-up schedules during edits.
            if (preg_match('/\A\[FOLLOW-UP SOURCE: ([0-9a-f-]{36})\]/i', (string) $schedule->notes, $matches)) {
                $marker = $matches[0];
                $userNotes = preg_replace('/\A\[FOLLOW-UP SOURCE: [0-9a-f-]{36}\]\s*/i', '', (string) ($validated['notes'] ?? ''));
                $validated['notes'] = $marker . ($userNotes !== '' ? "\n" . $userNotes : '');
            }
            $schedule->update($validated);
        }, 3);

        return redirect()
            ->route('maintenance.show', $maintenance->maintenance_schedule_id)
            ->with('success', 'Maintenance schedule successfully updated.');
    }

    /**
     * Hapus jadwal beserta seluruh record-nya.
     */
    public function destroy(MaintenanceSchedule $maintenance): RedirectResponse
    {
        DB::transaction(function () use ($maintenance): void {
            $schedule = MaintenanceSchedule::query()->whereKey($maintenance->getKey())
                ->lockForUpdate()->firstOrFail();
            if ($schedule->status !== 'Scheduled' || $schedule->records()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Only a Scheduled maintenance without records can be deleted. Cancel it instead.',
                ]);
            }
            $schedule->delete();
        }, 3);

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance schedule successfully deleted.');
    }

    /**
     * Form tambah record maintenance.
     */
    public function createRecord(MaintenanceSchedule $maintenance): View|RedirectResponse
    {
        if ($maintenance->status !== 'In Progress') {
            return redirect()->route('maintenance.show', $maintenance->getKey())
                ->with('error', 'Start maintenance before adding records. Finalized schedules are read-only.');
        }
        $maintenance->load(['serialNumber.product', 'serialNumber.customer', 'user']);
        return view('maintenance.records.create', $this->recordFormData($maintenance));
    }

    public function storeRecord(Request $request, MaintenanceSchedule $maintenance): RedirectResponse
    {
        $validated = $request->validate($this->recordRules());
        $completeSchedule = $request->boolean('complete_schedule');
        unset($validated['complete_schedule']);

        DB::transaction(function () use ($maintenance, $validated, $completeSchedule): void {
            $schedule = MaintenanceSchedule::query()->whereKey($maintenance->getKey())
                ->lockForUpdate()->firstOrFail();
            MaintenanceWorkflowRules::assertRecordWritable($schedule);
            $schedule->records()->create($validated);
            if ($completeSchedule) {
                MaintenanceWorkflowRules::assertTransition($schedule, 'Completed', 1);
                $schedule->update(['status' => 'Completed']);
            }
        }, 3);

        return redirect()->route('maintenance.show', $maintenance->getKey())
            ->with('success', 'Maintenance record successfully added.');
    }

    public function editRecord(MaintenanceSchedule $maintenance, MaintenanceRecord $record): View|RedirectResponse
    {
        $this->ensureRecordBelongsToSchedule($maintenance, $record);
        if ($maintenance->status !== 'In Progress') {
            return redirect()->route('maintenance.show', $maintenance->getKey())
                ->with('error', 'Maintenance records are read-only unless the schedule is In Progress.');
        }
        $maintenance->load(['serialNumber.product', 'serialNumber.customer', 'user']);
        return view('maintenance.records.edit', array_merge(
            ['record' => $record], $this->recordFormData($maintenance)
        ));
    }

    public function updateRecord(Request $request, MaintenanceSchedule $maintenance, MaintenanceRecord $record): RedirectResponse
    {
        $this->ensureRecordBelongsToSchedule($maintenance, $record);
        $validated = $request->validate($this->recordRules());
        $completeSchedule = $request->boolean('complete_schedule');
        unset($validated['complete_schedule']);

        DB::transaction(function () use ($maintenance, $record, $validated, $completeSchedule): void {
            $schedule = MaintenanceSchedule::query()->whereKey($maintenance->getKey())
                ->lockForUpdate()->firstOrFail();
            MaintenanceWorkflowRules::assertRecordWritable($schedule);
            $record->update($validated);
            if ($completeSchedule) {
                MaintenanceWorkflowRules::assertTransition($schedule, 'Completed', $schedule->records()->count());
                $schedule->update(['status' => 'Completed']);
            }
        }, 3);

        return redirect()->route('maintenance.show', $maintenance->getKey())
            ->with('success', 'Maintenance record successfully updated.');
    }

    public function destroyRecord(MaintenanceSchedule $maintenance, MaintenanceRecord $record): RedirectResponse
    {
        $this->ensureRecordBelongsToSchedule($maintenance, $record);
        DB::transaction(function () use ($maintenance, $record): void {
            $schedule = MaintenanceSchedule::query()->whereKey($maintenance->getKey())
                ->lockForUpdate()->firstOrFail();
            MaintenanceWorkflowRules::assertRecordWritable($schedule);
            $record->delete();
        }, 3);

        return redirect()->route('maintenance.show', $maintenance->getKey())
            ->with('success', 'Maintenance record successfully deleted.');
    }

    private function ensureRecordBelongsToSchedule(
        MaintenanceSchedule $maintenance,
        MaintenanceRecord $record
    ): void {
        abort_unless(
            $record->maintenance_schedule_id === $maintenance->maintenance_schedule_id,
            404
        );
    }

    private function recordRules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,user_id'],
            'performed_at' => ['required', 'date'],
            'result' => ['required', Rule::in(self::RESULTS)],
            'condition_before' => ['nullable', 'string'],
            'action_taken' => ['nullable', 'string'],
            'condition_after' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'complete_schedule' => ['nullable', 'boolean'],
        ];
    }

    private function recordFormData(MaintenanceSchedule $maintenance): array
    {
        return [
            'maintenance' => $maintenance,
            'users' => User::query()->orderBy('name')->get(),
            'results' => self::RESULTS,
        ];
    }

    private function scheduleRules(): array
    {
        return [
            'serial_number_id' => [
                'required',
                'uuid',
                'exists:serial_numbers,serial_number_id',
            ],
            'user_id' => [
                'nullable',
                'uuid',
                'exists:users,user_id',
            ],
            'scheduled_date' => ['required', 'date'],
            'maintenance_type' => ['required', Rule::in(self::TYPES)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function followupMarker(string $sourceId): string
    {
        return '[FOLLOW-UP SOURCE: ' . $sourceId . ']';
    }

    private function followupExists(MaintenanceSchedule $source): bool
    {
        return MaintenanceSchedule::query()
            ->where('notes', 'like', $this->followupMarker($source->getKey()) . '%')
            ->exists();
    }

    private function requiresFollowup(MaintenanceSchedule $source): bool
    {
        if ($source->status !== 'Completed') {
            return false;
        }
        $latest = $source->records()->orderByDesc('performed_at')
            ->orderByDesc('maintenance_record_id')->first();
        return $latest !== null
            && in_array($latest->result, ['Requires Follow-up', 'Failed'], true);
    }

    private function formData(): array
    {
        return [
            'serialNumbers' => SerialNumber::query()
                ->with(['product', 'customer'])
                ->orderBy('serial_number')
                ->get(),
            'users' => User::query()->orderBy('name')->get(),
            'statuses' => self::STATUSES,
            'types' => self::TYPES,
        ];
    }
}