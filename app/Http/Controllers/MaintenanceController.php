<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\SerialNumber;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
    public function create(): View
    {
        return view('maintenance.create', $this->formData());
    }

    /**
     * Simpan jadwal baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->scheduleRules());

        $maintenance = MaintenanceSchedule::create($validated);

        return redirect()
            ->route('maintenance.show', $maintenance->maintenance_schedule_id)
            ->with('success', 'Maintenance schedule successfully created.');
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

        return view('maintenance.show', [
            'maintenance' => $maintenance,
            'records' => $records,
            'allRecords' => $allRecords,
            'results' => self::RESULTS,
        ]);
    }

    /**
     * Form edit jadwal.
     */
    public function edit(MaintenanceSchedule $maintenance): View
    {
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

        $maintenance->update($validated);

        return redirect()
            ->route('maintenance.show', $maintenance->maintenance_schedule_id)
            ->with('success', 'Maintenance schedule successfully updated.');
    }

    /**
     * Hapus jadwal beserta seluruh record-nya.
     */
    public function destroy(MaintenanceSchedule $maintenance): RedirectResponse
    {
        DB::transaction(function () use ($maintenance) {
            MaintenanceRecord::where(
                'maintenance_schedule_id',
                $maintenance->maintenance_schedule_id
            )->delete();

            $maintenance->delete();
        });

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Maintenance schedule successfully deleted.');
    }

    /**
     * Form tambah record maintenance.
     */
    public function createRecord(MaintenanceSchedule $maintenance): View|RedirectResponse
    {
        if ($maintenance->status === 'Cancelled') {
            return redirect()
                ->route('maintenance.show', $maintenance->maintenance_schedule_id)
                ->with('error', 'Cannot add a record to a cancelled maintenance schedule.');
        }

        $maintenance->load(['serialNumber.product', 'serialNumber.customer', 'user']);

        return view('maintenance.records.create', $this->recordFormData($maintenance));
    }

    /**
     * Simpan record maintenance baru.
     */
    public function storeRecord(
        Request $request,
        MaintenanceSchedule $maintenance
    ): RedirectResponse {
        if ($maintenance->status === 'Cancelled') {
            return redirect()
                ->route('maintenance.show', $maintenance->maintenance_schedule_id)
                ->with('error', 'Cannot add a record to a cancelled maintenance schedule.');
        }

        $validated = $request->validate($this->recordRules());

        $completeSchedule = $request->boolean('complete_schedule');

        unset($validated['complete_schedule']);

        DB::transaction(function () use ($maintenance, $validated, $completeSchedule) {
            $maintenance->records()->create($validated);

            if ($completeSchedule) {
                $maintenance->update(['status' => 'Completed']);
            } elseif ($maintenance->status === 'Scheduled') {
                $maintenance->update(['status' => 'In Progress']);
            }
        });

        return redirect()
            ->route('maintenance.show', $maintenance->maintenance_schedule_id)
            ->with('success', 'Maintenance record successfully added.');
    }

    /**
     * Form edit record maintenance.
     */
    public function editRecord(
        MaintenanceSchedule $maintenance,
        MaintenanceRecord $record
    ): View {
        $this->ensureRecordBelongsToSchedule($maintenance, $record);

        $maintenance->load(['serialNumber.product', 'serialNumber.customer', 'user']);

        return view(
            'maintenance.records.edit',
            array_merge(
                ['record' => $record],
                $this->recordFormData($maintenance)
            )
        );
    }

    /**
     * Perbarui record maintenance.
     */
    public function updateRecord(
        Request $request,
        MaintenanceSchedule $maintenance,
        MaintenanceRecord $record
    ): RedirectResponse {
        $this->ensureRecordBelongsToSchedule($maintenance, $record);

        $validated = $request->validate($this->recordRules());

        $completeSchedule = $request->boolean('complete_schedule');

        unset($validated['complete_schedule']);

        DB::transaction(function () use ($maintenance, $record, $validated, $completeSchedule) {
            $record->update($validated);

            if ($completeSchedule && $maintenance->status !== 'Completed') {
                $maintenance->update(['status' => 'Completed']);
            }
        });

        return redirect()
            ->route('maintenance.show', $maintenance->maintenance_schedule_id)
            ->with('success', 'Maintenance record successfully updated.');
    }

    /**
     * Hapus satu record maintenance.
     */
    public function destroyRecord(
        MaintenanceSchedule $maintenance,
        MaintenanceRecord $record
    ): RedirectResponse {
        $this->ensureRecordBelongsToSchedule($maintenance, $record);

        $record->delete();

        return redirect()
            ->route('maintenance.show', $maintenance->maintenance_schedule_id)
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