<?php

namespace App\Http\Controllers;

use App\Models\ServiceTicket;
use App\Models\ServiceTicketPart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceTicketPartController extends Controller
{
    private function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,product_id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function editable(ServiceTicket $ticket): void
    {
        if ($ticket->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'parts' => 'Service parts can only be changed while the ticket is In Progress.',
            ]);
        }
    }

    public function store(Request $request, ServiceTicket $serviceTicket): RedirectResponse
    {
        $data = $request->validate($this->rules());
        DB::transaction(function () use ($serviceTicket, $data) {
            $ticket = ServiceTicket::query()->whereKey($serviceTicket->getKey())->lockForUpdate()->firstOrFail();
            $this->editable($ticket);
            $part = new ServiceTicketPart();
            $part->service_ticket_part_id = (string) Str::uuid();
            $part->service_ticket_id = $ticket->getKey();
            $part->product_id = $data['product_id'];
            $part->quantity = (int) $data['quantity'];
            $part->unit_price = $data['unit_price'];
            $part->notes = $data['notes'] ?? null;
            $part->save();
        }, 3);
        return redirect()->route('service-tickets.show', $serviceTicket)
            ->with('success', 'Service part added successfully.');
    }

    public function update(Request $request, ServiceTicket $serviceTicket, string $part): RedirectResponse
    {
        $data = $request->validate($this->rules());
        DB::transaction(function () use ($serviceTicket, $part, $data) {
            $ticket = ServiceTicket::query()->whereKey($serviceTicket->getKey())->lockForUpdate()->firstOrFail();
            $this->editable($ticket);
            $record = ServiceTicketPart::query()->whereKey($part)
                ->where('service_ticket_id', $ticket->getKey())->lockForUpdate()->firstOrFail();
            $record->update($data);
        }, 3);
        return redirect()->route('service-tickets.show', $serviceTicket)
            ->with('success', 'Service part updated successfully.');
    }

    public function destroy(ServiceTicket $serviceTicket, string $part): RedirectResponse
    {
        DB::transaction(function () use ($serviceTicket, $part) {
            $ticket = ServiceTicket::query()->whereKey($serviceTicket->getKey())->lockForUpdate()->firstOrFail();
            $this->editable($ticket);
            $record = ServiceTicketPart::query()->whereKey($part)
                ->where('service_ticket_id', $ticket->getKey())->lockForUpdate()->firstOrFail();
            $record->delete();
        }, 3);
        return redirect()->route('service-tickets.show', $serviceTicket)
            ->with('success', 'Service part removed successfully.');
    }
}
