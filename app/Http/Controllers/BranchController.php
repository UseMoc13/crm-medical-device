<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** A7.9.5.1 — branch master directory; protected by Super Admin route middleware. */
final class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'sort' => ['nullable', Rule::in(['branch_code', 'branch_name', 'status', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $query = Branch::query()->withCount('users')
            ->selectSub(DB::table('crm_teams')
                ->selectRaw('COUNT(*)')
                ->whereColumn('crm_teams.branch_id', 'branches.branch_id'), 'teams_count');

        if ($search !== '') {
            $pattern = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(function ($q) use ($pattern): void {
                $q->where('branch_code', 'ILIKE', $pattern)
                  ->orWhere('branch_name', 'ILIKE', $pattern)
                  ->orWhere('address', 'ILIKE', $pattern);
            });
        }
        if (! empty($filters['status'])) {
            $query->whereRaw('LOWER(status) = ?', [$filters['status']]);
        }
        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $branches = $query->orderBy($sort, $direction)
            ->orderBy('branch_id')->paginate(10)->withQueryString();

        $summary = [
            'total' => Branch::query()->count(),
            'active' => Branch::query()->whereRaw('LOWER(status) = ?', ['active'])->count(),
            'inactive' => Branch::query()->whereRaw('LOWER(status) = ?', ['inactive'])->count(),
            'assigned_users' => DB::table('users')->whereNotNull('branch_id')->count(),
        ];

        return view('branches.index', compact('branches', 'summary', 'filters'));
    }

    public function create(): View
    {
        return view('branches.create', ['branch' => new Branch()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $branch = DB::transaction(function () use ($data): Branch {
            $branch = new Branch($data);
            $branch->branch_id = (string) Str::uuid();
            $branch->save();
            $this->audit('INSERT', $branch, null, $this->snapshot($branch));
            return $branch;
        });

        return redirect()->route('branches.show', $branch)
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch): View
    {
        $users = $branch->users()->with('role')
            ->orderBy('name')->orderBy('user_id')
            ->paginate(10, ['*'], 'users_page')->withQueryString();
        $teams = DB::table('crm_teams')
            ->where('branch_id', $branch->getKey())
            ->whereNull('deleted_at')
            ->orderBy('team_name')->limit(10)
            ->get(['team_id', 'team_name', 'team_code', 'status']);
        $counts = [
            'users' => $branch->users()->count(),
            'teams' => DB::table('crm_teams')->where('branch_id', $branch->getKey())->count(),
        ];

        return view('branches.show', compact('branch', 'users', 'teams', 'counts'));
    }

    public function edit(Branch $branch): View
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $data = $this->validated($request, $branch);
        DB::transaction(function () use ($branch, $data): void {
            $locked = Branch::query()->whereKey($branch->getKey())->lockForUpdate()->firstOrFail();
            $old = $this->snapshot($locked);
            $locked->update($data);
            $new = $this->snapshot($locked);
            if ($old !== $new) {
                $this->audit('UPDATE', $locked, $old, $new);
            }
        });
        return redirect()->route('branches.show', $branch)
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        try {
            $deleted = DB::transaction(function () use ($branch): bool {
                $locked = Branch::query()->whereKey($branch->getKey())->lockForUpdate()->firstOrFail();
                if ($locked->users()->exists() || DB::table('crm_teams')
                    ->where('branch_id', $locked->getKey())->exists()) {
                    return false;
                }
                $before = $this->snapshot($locked);
                $locked->delete();
                $this->audit('DELETE', $locked, $before, null);
                return true;
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) !== '23503') {
                throw $e;
            }
            $deleted = false;
        }

        if (! $deleted) {
            return redirect()->route('branches.index')
                ->with('error', 'Branch cannot be deleted while used by Users or Teams. Set it to Inactive instead.');
        }
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }

    private function validated(Request $request, ?Branch $branch = null): array
    {
        if (is_string($request->input('branch_code'))) {
            $request->merge(['branch_code' => strtoupper(trim($request->input('branch_code')))]);
        }
        if (is_string($request->input('status'))) {
            $request->merge(['status' => strtolower(trim($request->input('status')))]);
        }

        return $request->validate([
            'branch_code' => [
                'required', 'string', 'max:30', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('branches', 'branch_code')->ignore($branch?->getKey(), 'branch_id'),
            ],
            'branch_name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'branch_code.regex' => 'Use letters A-Z, numbers, underscore or hyphen for the branch code.',
            'branch_code.unique' => 'This branch code already exists.',
        ]);
    }

    private function snapshot(Branch $branch): array
    {
        return $branch->only(['branch_code', 'branch_name', 'address', 'phone', 'status']);
    }

    private function audit(string $action, Branch $branch, ?array $old, ?array $new): void
    {
        DB::table('audit_logs')->insert([
            'audit_log_id' => (string) Str::uuid(),
            'user_id' => (string) auth('web')->id(),
            'action' => $action,
            'table_name' => 'branches',
            'record_id' => (string) $branch->getKey(),
            'old_value' => $old === null ? null : json_encode($old, JSON_THROW_ON_ERROR),
            'new_value' => $new === null ? null : json_encode($new, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
