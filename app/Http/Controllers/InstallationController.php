<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Installation;
use App\Models\InstallationChecklist;
use App\Models\SerialNumber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InstallationController extends Controller
{
    private const CHECKLIST = [
        'Verify device serial number and delivered items',
        'Inspect physical condition and accessories',
        'Complete installation and safety checks',
        'Perform operational test and customer handover',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['Scheduled', 'In Progress', 'Completed', 'Cancelled'])],
        ]);
        $query = Installation::with(['delivery', 'user']);
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $needle = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($needle) {
                $q->where('location', 'ilike', "%{$needle}%")
                    ->orWhere('notes', 'ilike', "%{$needle}%")
                    ->orWhereHas('delivery', fn ($d) => $d->where('delivery_number', 'ilike', "%{$needle}%"));
            });
        }
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        $installations = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        return view('installations.index', compact('installations'));
    }

    public function create(Request $request)
    {
        $deliveries = Delivery::query()->whereRaw('lower(status) = ?', ['delivered'])
            ->whereNotIn('delivery_id', Installation::query()->select('delivery_id'))
            ->orderByDesc('created_at')->get();
        $users = User::query()->where('status', 'active')->orderBy('name')->get();
        $prefillDeliveryId = (string) $request->query('delivery_id', '');
        return view('installations.create', compact('deliveries', 'users', 'prefillDeliveryId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'delivery_id' => ['required', 'uuid', 'exists:deliveries,delivery_id'],
            'user_id' => ['required', 'uuid', 'exists:users,user_id'],
            'installation_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
        $installation = DB::transaction(function () use ($data) {
            $delivery = Delivery::query()->whereKey($data['delivery_id'])->lockForUpdate()->firstOrFail();
            if (strtolower((string) $delivery->status) !== 'delivered') {
                throw ValidationException::withMessages(['delivery_id' => 'Only Delivered shipments can be scheduled for installation.']);
            }
            if (Installation::query()->where('delivery_id', $delivery->getKey())->exists()) {
                throw ValidationException::withMessages(['delivery_id' => 'An installation already exists for this Delivery.']);
            }
            if (!DeliveryItem::query()->where('delivery_id', $delivery->getKey())->exists()) {
                throw ValidationException::withMessages(['delivery_id' => 'This Delivery has no allocated serial numbers.']);
            }
            $installation = Installation::create([
                ...$data,
                'location' => $data['location'] ?? $delivery->delivery_address,
                'status' => 'Scheduled',
            ]);
            foreach (self::CHECKLIST as $item) {
                InstallationChecklist::create([
                    'installation_id' => $installation->getKey(),
                    'item_name' => $item,
                    'is_completed' => false,
                ]);
            }
            return $installation;
        });
        return redirect()->route('installations.show', $installation)->with('success', 'Installation scheduled and checklist generated.');
    }

    public function show(Installation $installation)
    {
        $installation->load(['delivery.salesOrder', 'user', 'checklists']);
        $deliveryItems = DeliveryItem::query()->with('serialNumber.product')
            ->where('delivery_id', $installation->delivery_id)->get();
        return view('installations.show', compact('installation', 'deliveryItems'));
    }

    public function edit(Installation $installation)
    {
        if (in_array($installation->status, ['Completed', 'Cancelled'], true)) {
            return redirect()->route('installations.show', $installation)->with('error', 'Finalized installations cannot be edited.');
        }
        $deliveries = Delivery::query()->whereKey($installation->delivery_id)->get();
        $users = User::query()->orderBy('name')->get();
        return view('installations.edit', compact('installation', 'deliveries', 'users'));
    }

    public function update(Request $request, Installation $installation)
    {
        $data = $request->validate([
            'delivery_id' => ['required', 'uuid', Rule::in([$installation->delivery_id])],
            'user_id' => ['required', 'uuid', 'exists:users,user_id'],
            'installation_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Scheduled', 'In Progress', 'Completed', 'Cancelled'])],
            'notes' => ['nullable', 'string'],
        ]);
        DB::transaction(function () use ($installation, $data) {
            $record = Installation::query()->whereKey($installation->getKey())->lockForUpdate()->firstOrFail();
            $transitions = [
                'Scheduled' => ['Scheduled', 'In Progress', 'Cancelled'],
                'In Progress' => ['In Progress', 'Completed'],
                'Completed' => ['Completed'],
                'Cancelled' => ['Cancelled'],
            ];
            if (!in_array($data['status'], $transitions[$record->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Invalid installation status transition.']);
            }
            if ($record->status === 'Completed' || $record->status === 'Cancelled') {
                throw ValidationException::withMessages(['status' => 'Finalized installations cannot be edited.']);
            }
            if ($data['status'] === 'Completed') {
                $delivery = Delivery::query()->whereKey($record->delivery_id)->lockForUpdate()->firstOrFail();
                if (strtolower((string) $delivery->status) !== 'delivered') {
                    throw ValidationException::withMessages(['status' => 'Delivery must be Delivered before completing Installation.']);
                }
                $total = InstallationChecklist::where('installation_id', $record->getKey())->count();
                $pending = InstallationChecklist::where('installation_id', $record->getKey())->where('is_completed', false)->count();
                if ($total === 0 || $pending > 0) {
                    throw ValidationException::withMessages(['status' => 'Complete all installation checklist items first.']);
                }
                $items = DeliveryItem::query()->where('delivery_id', $record->delivery_id)->get();
                if ($items->isEmpty()) {
                    throw ValidationException::withMessages(['status' => 'Delivery has no allocated device serial numbers.']);
                }
                foreach ($items as $item) {
                    $serial = SerialNumber::query()->whereKey($item->serial_number_id)->lockForUpdate()->firstOrFail();
                    if ($serial->status !== 'assigned') {
                        throw ValidationException::withMessages(['status' => 'Serial number ' . $serial->serial_number . ' must be Assigned before Installation can complete.']);
                    }
                    $serial->status = 'installed';
                    $serial->save();
                }
            }
            $record->update($data);
        });
        return redirect()->route('installations.show', $installation)->with('success', 'Installation updated successfully.');
    }


    /** Safely transition an installation from its detail page. */
    public function changeStatus(Request $request, Installation $installation)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['In Progress', 'Completed', 'Cancelled'])],
        ]);

        DB::transaction(function () use ($installation, $data) {
            $record = Installation::query()->whereKey($installation->getKey())->lockForUpdate()->firstOrFail();
            $next = $data['status'];
            $allowed = [
                'Scheduled' => ['In Progress', 'Cancelled'],
                'In Progress' => ['Completed'],
            ];
            if (!in_array($next, $allowed[$record->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'This installation status transition is not allowed.']);
            }
            if ($next === 'Completed') {
                $delivery = Delivery::query()->whereKey($record->delivery_id)->lockForUpdate()->firstOrFail();
                if (strtolower((string) $delivery->status) !== 'delivered') {
                    throw ValidationException::withMessages(['status' => 'The linked Delivery must be Delivered.']);
                }
                $total = InstallationChecklist::where('installation_id', $record->getKey())->count();
                $pending = InstallationChecklist::where('installation_id', $record->getKey())->where('is_completed', false)->count();
                if ($total === 0 || $pending > 0) {
                    throw ValidationException::withMessages(['status' => 'Complete every checklist item before finishing installation.']);
                }
                $items = DeliveryItem::query()->where('delivery_id', $record->delivery_id)->get();
                if ($items->isEmpty()) {
                    throw ValidationException::withMessages(['status' => 'There are no delivery devices to install.']);
                }
                $serialIds = $items->pluck('serial_number_id');
                if ($serialIds->filter()->count() !== $items->count() || $serialIds->unique()->count() !== $items->count()) {
                    throw ValidationException::withMessages(['status' => 'Delivery device serial numbers must be unique and complete.']);
                }
                // Lock all serialized units before making any changes.
                $serials = SerialNumber::query()->whereIn('serial_number_id', $serialIds)->orderBy('serial_number_id')->lockForUpdate()->get();
                if ($serials->count() !== $items->count()) {
                    throw ValidationException::withMessages(['status' => 'Some device serial numbers could not be found.']);
                }
                foreach ($serials as $serial) {
                    if (strtolower((string) $serial->status) !== 'assigned') {
                        throw ValidationException::withMessages(['status' => 'Serial ' . $serial->serial_number . ' must be assigned before completion.']);
                    }
                }
                foreach ($serials as $serial) {
                    $serial->status = 'installed';
                    $serial->save();
                }
            }
            $record->status = $next;
            $record->save();
        });
        return redirect()->route('installations.show', $installation)->with('success', 'Installation status updated successfully.');
    }

    public function updateChecklist(Request $request, Installation $installation, InstallationChecklist $checklist)
    {
        $data = $request->validate([
            'is_completed' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($installation, $checklist, $data) {
            $record = Installation::query()->whereKey($installation->getKey())->lockForUpdate()->firstOrFail();
            if ($record->status !== 'In Progress' || $checklist->installation_id !== $record->getKey()) {
                throw ValidationException::withMessages(['checklist' => 'Checklist can only be edited for an In Progress installation.']);
            }
            $checklist->update([
                'is_completed' => (bool) $data['is_completed'],
                'notes' => $data['notes'] ?? $checklist->notes,
                'completed_at' => $data['is_completed'] ? now() : null,
            ]);
        });
        return redirect()->route('installations.show', $installation)->with('success', 'Checklist updated.');
    }

    public function destroy(Installation $installation)
    {
        DB::transaction(function () use ($installation) {
            $record = Installation::query()->whereKey($installation->getKey())->lockForUpdate()->firstOrFail();
            if ($record->status !== 'Scheduled') {
                throw ValidationException::withMessages(['installation' => 'Only Scheduled installations can be deleted.']);
            }
            InstallationChecklist::where('installation_id', $record->getKey())->delete();
            $record->delete();
        });
        return redirect()->route('installations.index')->with('success', 'Scheduled Installation deleted.');
    }
}
