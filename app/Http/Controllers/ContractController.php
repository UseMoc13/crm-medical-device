<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    /**
     * Menampilkan list contract.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'expired', 'terminated'])],
            'contract_type' => ['nullable', Rule::in(['Sales Contract', 'Service Agreement', 'Maintenance Contract', 'Rental Agreement', 'Distribution Agreement', 'Other'])],
        ]);
        $query = Contract::query()->with('salesOrder');
        if ($search = trim($filters['search'] ?? '')) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('contract_number', 'ilike', "%{$escaped}%")
                    ->orWhereHas('salesOrder', fn ($order) => $order->where('sales_order_number', 'ilike', "%{$escaped}%"));
            });
        }
        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (! empty($filters['contract_type'])) $query->where('contract_type', $filters['contract_type']);
        $contracts = $query->orderByDesc('created_at')->orderByDesc('contract_id')->paginate(10)->withQueryString();
        return view('contracts.index', compact('contracts'));
    }

    /**
     * Menampilkan form pembuatan contract.
     */
    public function create()
    {
        $salesOrders = SalesOrder::all();

        return view('contracts.create', compact('salesOrders'));
    }

    /**
     * Menyimpan hasil pembuatan contract.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'sales_order_id' => [
            'required',
            'uuid',
            'exists:sales_orders,sales_order_id',
            'unique:contracts,sales_order_id',
        ],
        'start_date' => ['required', 'date'],
        'end_date' => [
            'nullable',
            'date',
            'after_or_equal:start_date',
        ],
        'contract_type' => [
            'nullable',
            Rule::in([
                'Sales Contract',
                'Service Agreement',
                'Maintenance Contract',
                'Rental Agreement',
                'Distribution Agreement',
                'Other',
            ]),
        ],
        'status' => [
            'required',
            Rule::in([
                'draft',
                'active',
                'expired',
                'terminated',
            ]),
        ],
        'document' => [
            'nullable',
            'file',
            'mimes:pdf',
            'max:5120',
        ],
    ]);

    // File kontrak disimpan pada storage privat.
    $documentPath = null;

    if ($request->hasFile('document')) {
        $documentPath = $request->file('document')
            ->store('contracts', 'local');
    }

    try {
        $contract = DB::transaction(function () use (
            $validated,
            $documentPath
        ) {
            $year = now()->year;

            // Mencegah proses paralel menggunakan nomor yang sama.
            DB::select(
                'SELECT pg_advisory_xact_lock(?)',
                [$year]
            );

            $prefix = 'CTR-' . $year . '-';

            $lastContract = Contract::where(
                'contract_number',
                'like',
                $prefix . '%'
            )
                ->orderByDesc('contract_number')
                ->first();

            $lastNumber = $lastContract
                ? (int) substr(
                    $lastContract->contract_number,
                    strlen($prefix)
                )
                : 0;

            $validated['contract_number'] = $prefix
                . str_pad(
                    $lastNumber + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $validated['document_path'] = $documentPath;

            return Contract::create($validated);
        });
    } catch (\Throwable $e) {
        // Hapus file jika penyimpanan data kontrak gagal.
        if ($documentPath) {
            Storage::disk('local')->delete($documentPath);
        }

        throw $e;
    }

    return redirect()
        ->route('contracts.index')
        ->with(
            'success',
            'Contract ' . $contract->contract_number
                . ' berhasil dibuat.'
        );
}

    /**
     * Menampilkan contract spesifik.
     */
    public function show(Contract $contract)
    {
        $contract->load('salesOrder');

        return view('contracts.show', compact('contract'));
    }

    /**
     * Menampilkan form untuk edit contract.
     */
    public function edit(Contract $contract)
    {
        $salesOrders = SalesOrder::all();

        return view(
            'contracts.edit',
            compact('contract', 'salesOrders')
        );
    }

    /**
     * Update spesifik contract.
     */
    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'sales_order_id' => [
                'required',
                'uuid',
                'exists:sales_orders,sales_order_id',
                Rule::unique('contracts', 'sales_order_id')
                    ->ignore($contract->contract_id, 'contract_id'),
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'contract_type' => [
                'nullable',
                Rule::in([
                    'Sales Contract',
                    'Service Agreement',
                    'Maintenance Contract',
                    'Rental Agreement',
                    'Distribution Agreement',
                    'Other',
                ]),
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'active', 'expired', 'terminated']),
            ],
            'document' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $oldDocument = $contract->document_path;
        $newDocument = $request->hasFile('document') ? $request->file('document')->store('contracts', 'local') : null;
        unset($validated['document']);
        if ($newDocument) $validated['document_path'] = $newDocument;
        try {
            $contract->update($validated);
        } catch (\Throwable $e) {
            if ($newDocument) Storage::disk('local')->delete($newDocument);
            throw $e;
        }
        // Clean up the replaced file only after DB update succeeds.
        if ($newDocument && $oldDocument && $oldDocument !== $newDocument) Storage::disk('local')->delete($oldDocument);

        return redirect()
            ->route('contracts.index')
            ->with('success', 'Contract berhasil diperbarui.');
    }

    /**
     * Menghapus spesifik contract.
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();

        return redirect()
            ->route('contracts.index')
            ->with('success', 'Contract berhasil dihapus.');
    }

    public function download(Contract $contract)
{
    if (!$contract->document_path) {
        return redirect()
            ->route('contracts.show', $contract->contract_id)
            ->with('error', 'Dokumen kontrak belum tersedia.');
    }

    $disk = Storage::disk('local');

    if (!$disk->exists($contract->document_path)) {
        return redirect()
            ->route('contracts.show', $contract->contract_id)
            ->with('error', 'File dokumen kontrak tidak ditemukan.');
    }

    return $disk->download(
        $contract->document_path,
        $contract->contract_number . '.pdf'
    );
}
}
