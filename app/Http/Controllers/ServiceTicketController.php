<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\SerialNumber;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceTicketController extends Controller
{
    private const STATUSES = [
        'open',
        'in_progress',
        'on_hold',
        'resolved',
        'closed',
    ];

    private const PRIORITIES = [
        'low',
        'medium',
        'high',
        'urgent',
    ];

    public function index(Request $request): View
    {
        $query = ServiceTicket::query()
            ->with([
                'customer',
                'serialNumber.product',
                'user',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                    ->orWhere('title', 'ILIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where(
                            'customer_name',
                            'ILIKE',
                            "%{$search}%"
                        );
                    })
                    ->orWhereHas('serialNumber', function ($serialQuery) use ($search) {
                        $serialQuery->where(
                            'serial_number',
                            'ILIKE',
                            "%{$search}%"
                        );
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $allowedSorts = [
            'ticket_number',
            'title',
            'priority',
            'status',
            'opened_at',
        ];

        $sort = in_array($request->sort, $allowedSorts, true)
            ? $request->sort
            : 'opened_at';

        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $serviceTickets = $query
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('service-tickets.index', [
            'serviceTickets' => $serviceTickets,
            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view(
            'service-tickets.create',
            $this->formData()
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],
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
            'title' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'priority' => [
                'required',
                Rule::in(self::PRIORITIES),
            ],
            'status' => [
                'required',
                Rule::in(self::STATUSES),
            ],
        ]);

        if (!$this->deviceBelongsToCustomer(
            $validated['serial_number_id'],
            $validated['customer_id']
        )) {
            return back()
                ->withErrors([
                    'serial_number_id' => 'The selected device is assigned to a different customer.',
                ])
                ->withInput();
        }

        $validated['ticket_number'] = $this->generateTicketNumber();
        $validated['opened_at'] = now();
        $validated['closed_at'] = $validated['status'] === 'closed'
            ? now()
            : null;

        ServiceTicket::create($validated);

        return redirect()
            ->route('service-tickets.index')
            ->with('success', 'Service ticket successfully created.');
    }

    public function show(ServiceTicket $serviceTicket): View
    {
        $serviceTicket->load([
            'customer',
            'serialNumber.product',
            'user',
            'parts.product',
        ]);

        return view('service-tickets.show', [
            'serviceTicket' => $serviceTicket,
        ]);
    }

    public function edit(ServiceTicket $serviceTicket): View
    {
        $serviceTicket->load([
            'customer',
            'serialNumber.product',
            'user',
        ]);

        return view(
            'service-tickets.edit',
            array_merge(
                [
                    'serviceTicket' => $serviceTicket,
                ],
                $this->formData()
            )
        );
    }

    public function update(
        Request $request,
        ServiceTicket $serviceTicket
    ): RedirectResponse {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'uuid',
                'exists:customers,customer_id',
            ],
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
            'title' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'priority' => [
                'required',
                Rule::in(self::PRIORITIES),
            ],
            'status' => [
                'required',
                Rule::in(self::STATUSES),
            ],
            'resolution' => [
                'nullable',
                'string',
            ],
        ]);

        if (!$this->deviceBelongsToCustomer(
            $validated['serial_number_id'],
            $validated['customer_id']
        )) {
            return back()
                ->withErrors([
                    'serial_number_id' => 'The selected device is assigned to a different customer.',
                ])
                ->withInput();
        }

        $validated['closed_at'] = $validated['status'] === 'closed'
            ? ($serviceTicket->closed_at ?? now())
            : null;

        $serviceTicket->update($validated);

        return redirect()
            ->route('service-tickets.show', $serviceTicket)
            ->with('success', 'Service ticket successfully updated.');
    }

    private function formData(): array
    {
        $customers = Customer::query()
            ->orderBy('customer_name')
            ->get();

        $serialNumbers = SerialNumber::query()
            ->with('product')
            ->orderBy('serial_number')
            ->get();

        $users = User::query()
            ->orderBy('name')
            ->get();

        return [
            'customers' => $customers,
            'serialNumbers' => $serialNumbers,

            // $users digunakan oleh halaman Create.
            'users' => $users,

            // Alias untuk kompatibilitas dengan halaman lain.
            'technicians' => $users,

            'statuses' => self::STATUSES,
            'priorities' => self::PRIORITIES,
        ];
    }

    private function deviceBelongsToCustomer(
        string $serialNumberId,
        string $customerId
    ): bool {
        $serialNumber = SerialNumber::query()
            ->find($serialNumberId);

        if (!$serialNumber) {
            return false;
        }

        // Perangkat tanpa customer masih dapat dipilih.
        // Jika sudah memiliki customer, harus cocok dengan tiket.
        return empty($serialNumber->customer_id)
            || $serialNumber->customer_id === $customerId;
    }

    private function generateTicketNumber(): string
    {
        do {
            $ticketNumber = 'SRV-' . now()->format('Ymd') . '-' .
                Str::upper(Str::random(6));
        } while (
            ServiceTicket::where('ticket_number', $ticketNumber)->exists()
        );

        return $ticketNumber;
    }
}
