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
    public function index()
    {
        $contracts = Contract::with('salesOrder')
            ->orderByDesc('created_at')
            ->paginate(10);

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
            'contract_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contracts', 'contract_number')
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
            'document_path' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $contract->update($validated);

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
