<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\SerialNumber;
use App\Models\ServiceTicket;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Service History (read-only).
 *
 * Menggabungkan service_tickets dan maintenance_records menjadi satu
 * riwayat layanan. Tidak membutuhkan tabel baru.
 */
class ServiceHistoryController extends Controller
{
    /**
     * Feed gabungan seluruh riwayat layanan.
     */
    public function index(Request $request): View
    {
        $request->validate([
            'type' => ['nullable', 'in:ticket,maintenance,work_log,resolution'],
            'customer_id' => ['nullable', 'uuid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = DB::query()
            ->fromSub($this->eventsQuery(), 'h')
            ->leftJoin('serial_numbers as sn', 'sn.serial_number_id', '=', 'h.serial_number_id')
            ->leftJoin('products as p', 'p.product_id', '=', 'sn.product_id')
            ->leftJoin('customers as c', 'c.customer_id', '=', 'h.customer_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'h.user_id')
            ->select(
                'h.*',
                'sn.serial_number',
                'p.product_name',
                'c.customer_name',
                'u.name as technician_name'
            );

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('h.reference', 'ILIKE', "%{$search}%")
                    ->orWhere('h.summary', 'ILIKE', "%{$search}%")
                    ->orWhere('sn.serial_number', 'ILIKE', "%{$search}%")
                    ->orWhere('p.product_name', 'ILIKE', "%{$search}%")
                    ->orWhere('c.customer_name', 'ILIKE', "%{$search}%")
                    ->orWhere('u.name', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('h.event_type', $request->input('type'));
        }

        if ($request->filled('customer_id')) {
            $query->where('h.customer_id', $request->input('customer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('h.event_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('h.event_date', '<=', $request->input('date_to'));
        }

        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $events = $query
            ->orderBy('h.event_date', $direction)
            ->orderBy('h.source_id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total' => DB::query()->fromSub($this->eventsQuery(), 'h')->count(),
            'tickets' => DB::table('service_tickets')->count(),
            'maintenance' => DB::table('maintenance_records')->count(),
            'work_logs' => DB::table('service_ticket_work_logs')->count(),
            'resolutions' => DB::table('service_ticket_resolution_logs')->count(),
            'devices' => DB::query()
                ->fromSub($this->eventsQuery(), 'h')
                ->distinct()
                ->count('h.serial_number_id'),
            'last_30_days' => DB::query()
                ->fromSub($this->eventsQuery(), 'h')
                ->whereDate('h.event_date', '>=', now()->subDays(30)->toDateString())
                ->count(),
        ];

        return view('service-history.index', [
            'events' => $events,
            'summary' => $summary,
            'customers' => Customer::query()->orderBy('customer_name')->get(),
            'direction' => $direction,
        ]);
    }

    /**
     * Riwayat layanan lengkap untuk satu perangkat (serial number).
     */
    public function show(SerialNumber $serialNumber): View
    {
        $serialNumber->load(['product', 'customer']);

        $serialId = $serialNumber->serial_number_id;

        $tickets = ServiceTicket::query()
            ->where('serial_number_id', $serialId)
            ->with(['user', 'parts.product'])
            ->get();

        $records = MaintenanceRecord::query()
            ->whereHas('schedule', function ($q) use ($serialId) {
                $q->where('serial_number_id', $serialId);
            })
            ->with(['user', 'schedule'])
            ->get();

        $upcoming = MaintenanceSchedule::query()
            ->where('serial_number_id', $serialId)
            ->whereIn('status', ['Scheduled', 'In Progress'])
            ->with('user')
            ->orderBy('scheduled_date')
            ->get();

        $warranty = DB::table('warranties')
            ->where('serial_number_id', $serialId)
            ->orderByDesc('end_date')
            ->first();

        // Normalisasi ke satu bentuk timeline.
        $events = collect();

        foreach ($tickets as $ticket) {
            $partsCost = $ticket->parts->sum(function ($part) {
                return (float) ($part->unit_price ?? 0) * (int) ($part->quantity ?? 0);
            });

            $events->push([
                'type' => 'ticket',
                'date' => Carbon::parse($ticket->opened_at),
                'reference' => $ticket->ticket_number,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'outcome' => $ticket->status,
                'technician' => $ticket->user?->name,
                'url' => route('service-tickets.show', $ticket->service_ticket_id),
                'details' => array_filter([
                    'Priority' => ucfirst((string) $ticket->priority),
                    'Closed At' => $ticket->closed_at
                        ? Carbon::parse($ticket->closed_at)->format('d M Y, H:i')
                        : null,
                    'Resolution' => $ticket->resolution,
                    'Parts Used' => $ticket->parts->count()
                        ? $ticket->parts->count() . ' item(s) — Rp ' . number_format($partsCost, 0, ',', '.')
                        : null,
                ], fn ($value) => filled($value)),
            ]);
        }

        // Fetch child events in bulk to avoid N+1 queries. Work logs and resolutions
        // have their own timestamps and should appear as separate timeline events.
        $ticketIds = $tickets->pluck('service_ticket_id');
        $ticketLookup = $tickets->keyBy('service_ticket_id');
        $workLogs = $ticketIds->isEmpty() ? collect() : DB::table('service_ticket_work_logs')
            ->whereIn('service_ticket_id', $ticketIds)->orderBy('created_at')->get();
        $resolutionLogs = $ticketIds->isEmpty() ? collect() : DB::table('service_ticket_resolution_logs')
            ->whereIn('service_ticket_id', $ticketIds)->orderBy('created_at')->get();

        foreach ($workLogs as $log) {
            $ticket = $ticketLookup->get($log->service_ticket_id);
            if (!$ticket) continue;
            $events->push([
                'type' => 'work_log',
                'date' => Carbon::parse($log->created_at),
                'reference' => $ticket->ticket_number,
                'title' => ucwords(str_replace('_', ' ', (string) $log->entry_type)),
                'description' => $log->notes,
                'outcome' => $log->status_snapshot,
                'technician' => $ticket->user?->name,
                'url' => route('service-tickets.show', $ticket->service_ticket_id) . '#ticket-work-logs',
                'details' => ['Ticket' => $ticket->title],
            ]);
        }

        foreach ($resolutionLogs as $log) {
            $ticket = $ticketLookup->get($log->service_ticket_id);
            if (!$ticket) continue;
            $events->push([
                'type' => 'resolution',
                'date' => Carbon::parse($log->created_at),
                'reference' => $ticket->ticket_number,
                'title' => 'Resolution / Status Update',
                'description' => $log->resolution,
                'outcome' => $log->status_to,
                'technician' => $ticket->user?->name,
                'url' => route('service-tickets.show', $ticket->service_ticket_id),
                'details' => ['Transition' => ucwords(str_replace('_', ' ', (string) $log->status_from)) . ' → ' . ucwords(str_replace('_', ' ', (string) $log->status_to))],
            ]);
        }

        foreach ($records as $record) {
            $events->push([
                'type' => 'maintenance',
                'date' => Carbon::parse($record->performed_at),
                'reference' => $record->schedule?->maintenance_type . ' Maintenance',
                'title' => $record->action_taken ?: 'Maintenance performed',
                'description' => null,
                'outcome' => $record->result,
                'technician' => $record->user?->name,
                'url' => route('maintenance.show', $record->maintenance_schedule_id),
                'details' => array_filter([
                    'Condition Before' => $record->condition_before,
                    'Condition After' => $record->condition_after,
                    'Notes' => $record->notes,
                ], fn ($value) => filled($value)),
            ]);
        }

        $events = $events->sortByDesc(fn ($event) => $event['date']->timestamp)->values();

        $stats = [
            'total' => $events->count(),
            'work_logs' => $workLogs->count(),
            'resolutions' => $resolutionLogs->count(),
            'tickets' => $tickets->count(),
            'open_tickets' => $tickets->whereNotIn('status', ['resolved', 'closed'])->count(),
            'maintenance' => $records->count(),
            'last_service' => $events->first()['date'] ?? null,
            'parts_cost' => $tickets->sum(function ($ticket) {
                return $ticket->parts->sum(function ($part) {
                    return (float) ($part->unit_price ?? 0) * (int) ($part->quantity ?? 0);
                });
            }),
        ];

        return view('service-history.show', [
            'serialNumber' => $serialNumber,
            'timeline' => $events->groupBy(fn ($event) => $event['date']->format('F Y')),
            'stats' => $stats,
            'upcoming' => $upcoming,
            'warranty' => $warranty,
        ]);
    }

    /**
     * Gabungan service_tickets + maintenance_records dengan kolom seragam.
     * Dibuat baru pada setiap pemanggilan karena Builder bersifat mutable.
     */
    private function eventsQuery(): Builder
    {
        $tickets = DB::table('service_tickets as st')->select([
            DB::raw("'ticket'::text AS event_type"),
            'st.service_ticket_id AS source_id',
            DB::raw('NULL::uuid AS parent_id'),
            'st.opened_at AS event_date',
            'st.ticket_number AS reference',
            'st.title AS summary',
            'st.status AS outcome',
            'st.priority AS detail',
            'st.serial_number_id AS serial_number_id',
            'st.customer_id AS customer_id',
            'st.user_id AS user_id',
        ]);

        $maintenance = DB::table('maintenance_records as mr')
            ->join('maintenance_schedules as ms', 'ms.maintenance_schedule_id', '=', 'mr.maintenance_schedule_id')
            ->join('serial_numbers as msn', 'msn.serial_number_id', '=', 'ms.serial_number_id')
            ->select([
                DB::raw("'maintenance'::text AS event_type"),
                'mr.maintenance_record_id AS source_id',
                'ms.maintenance_schedule_id AS parent_id',
                'mr.performed_at AS event_date',
                'ms.maintenance_type AS reference',
                DB::raw('COALESCE(mr.action_taken, mr.notes) AS summary'),
                'mr.result AS outcome',
                'ms.status AS detail',
                'ms.serial_number_id AS serial_number_id',
                'msn.customer_id AS customer_id',
                'mr.user_id AS user_id',
            ]);

        $workLogs = DB::table('service_ticket_work_logs as wl')
            ->join('service_tickets as st', 'st.service_ticket_id', '=', 'wl.service_ticket_id')
            ->select([
                DB::raw("'work_log'::text AS event_type"),
                'wl.work_log_id AS source_id',
                'st.service_ticket_id AS parent_id',
                'wl.created_at AS event_date',
                'st.ticket_number AS reference',
                'wl.notes AS summary',
                'wl.status_snapshot AS outcome',
                'wl.entry_type AS detail',
                'st.serial_number_id AS serial_number_id',
                'st.customer_id AS customer_id',
                'st.user_id AS user_id',
            ]);

        $resolutions = DB::table('service_ticket_resolution_logs as rl')
            ->join('service_tickets as st', 'st.service_ticket_id', '=', 'rl.service_ticket_id')
            ->select([
                DB::raw("'resolution'::text AS event_type"),
                'rl.resolution_log_id AS source_id',
                'st.service_ticket_id AS parent_id',
                'rl.created_at AS event_date',
                'st.ticket_number AS reference',
                'rl.resolution AS summary',
                'rl.status_to AS outcome',
                'rl.status_from AS detail',
                'st.serial_number_id AS serial_number_id',
                'st.customer_id AS customer_id',
                'st.user_id AS user_id',
            ]);

        return $tickets->unionAll($maintenance)->unionAll($workLogs)->unionAll($resolutions);
    }
}
