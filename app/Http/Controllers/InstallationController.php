<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Installation;
use App\Models\InstallationChecklist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InstallationController extends Controller
{
    /**
     * Daftar instalasi.
     */
    public function index(Request $request)
    {
        $query = Installation::with([
            'delivery',
            'user',
        ]);

        // Pencarian berdasarkan lokasi atau catatan.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('location', 'ilike', "%{$search}%")
                    ->orWhere('notes', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%")
                    ->orWhereHas('delivery', function ($deliveryQuery) use ($search) {
                        $deliveryQuery->where(
                            'delivery_number',
                            'ilike',
                            "%{$search}%"
                        );
                    });
            });
        }

        // Filter status.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $installations = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('installations.index', compact('installations'));
    }

    /**
     * Form tambah instalasi.
     */
    public function create()
    {
        $deliveries = Delivery::orderByDesc('created_at')->get();
        $users = User::all();

        return view('installations.create', compact(
            'deliveries',
            'users'
        ));
    }

    /**
     * Simpan instalasi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_id' => [
                'required',
                'uuid',
                'exists:deliveries,delivery_id',
            ],
            'user_id' => [
                'required',
                'uuid',
                'exists:users,user_id',
            ],
            'installation_date' => [
                'nullable',
                'date',
            ],
            'location' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::in([
                    'Scheduled',
                    'In Progress',
                    'Completed',
                    'Cancelled',
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        Installation::create($validated);

        return redirect()
            ->route('installations.index')
            ->with('success', 'Installation berhasil dibuat.');
    }

    /**
     * Detail instalasi.
     */
    public function show(Installation $installation)
    {
        $installation->load([
            'delivery',
            'user',
            'checklists',
        ]);

        return view('installations.show', compact('installation'));
    }

    /**
     * Form edit instalasi.
     */
    public function edit(Installation $installation)
    {
        $deliveries = Delivery::orderByDesc('created_at')->get();
        $users = User::all();

        return view('installations.edit', compact(
            'installation',
            'deliveries',
            'users'
        ));
    }

    /**
     * Perbarui instalasi.
     */
    public function update(
        Request $request,
        Installation $installation
    ) {
        $validated = $request->validate([
            'delivery_id' => [
                'required',
                'uuid',
                'exists:deliveries,delivery_id',
            ],
            'user_id' => [
                'required',
                'uuid',
                'exists:users,user_id',
            ],
            'installation_date' => [
                'nullable',
                'date',
            ],
            'location' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                Rule::in([
                    'Scheduled',
                    'In Progress',
                    'Completed',
                    'Cancelled',
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $installation->update($validated);

        return redirect()
            ->route('installations.show', $installation->installation_id)
            ->with('success', 'Installation berhasil diperbarui.');
    }

    /**
     * Hapus instalasi beserta checklist-nya
     */
    public function destroy(Installation $installation)
    {
        DB::transaction(function () use ($installation) {
            InstallationChecklist::where(
                'installation_id',
                $installation->installation_id
            )->delete();

            $installation->delete();
        });

        return redirect()
            ->route('installations.index')
            ->with('success', 'Installation berhasil dihapus.');
    }
}