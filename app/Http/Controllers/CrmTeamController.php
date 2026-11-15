<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CrmTeam;
use App\Models\Role;
use App\Models\User;
use App\Services\CrmTeamMembershipManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class CrmTeamController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(['permanent', 'project'])],
            'status' => ['nullable', Rule::in(['active', 'archived'])],
            'branch_id' => ['nullable', 'uuid', 'exists:branches,branch_id'],
        ]);
        $query = CrmTeam::query()->with(['branch', 'leader'])
            ->withCount(['activeMemberships as active_members_count']);
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $escaped = '%' . addcslashes($search, '%_\\') . '%';
            $query->where(fn ($q) => $q->where('team_name', 'ilike', $escaped)->orWhere('team_code', 'ilike', $escaped));
        }
        foreach (['team_type' => 'type', 'status' => 'status', 'branch_id' => 'branch_id'] as $column => $input) {
            if (!empty($filters[$input])) $query->where($column, $filters[$input]);
        }
        $teams = $query->orderByDesc('created_at')->orderBy('team_code')->paginate(10)->withQueryString();
        $summary = [
            'total' => CrmTeam::query()->count(),
            'active' => CrmTeam::query()->where('status', 'active')->count(),
            'permanent' => CrmTeam::query()->where('team_type', 'permanent')->count(),
            'project' => CrmTeam::query()->where('team_type', 'project')->count(),
        ];
        $branches = Branch::query()->orderBy('branch_name')->get();
        return view('teams.index', compact('teams', 'summary', 'branches'));
    }

    public function create(): View
    {
        return view('teams.create', $this->editorData());
    }

    public function store(Request $request, CrmTeamMembershipManager $memberships): RedirectResponse
    {
        $data = $this->validated($request);
        $ids = $data['member_user_ids'] ?? [];
        unset($data['member_user_ids']);
        $data['created_by_user_id'] = (string) Auth::id();

        $team = DB::transaction(function () use ($data, $ids, $memberships): CrmTeam {
            $memberships->validateSelection(
                $ids, $data['leader_user_id'] ?? null, $data['team_type'],
                $data['branch_id'] ?? null, $data['status']
            );
            $team = CrmTeam::create($data);
            $memberships->sync($team, $ids);
            $this->audit('CREATE', $team, null, ['team' => $team->only(['team_code','team_name','team_type','status','branch_id','leader_user_id']), 'member_user_ids' => $ids]);
            return $team;
        });
        return redirect()->route('teams.show', $team)->with('success', 'Team created successfully.');
    }

    public function show(CrmTeam $team): View
    {
        $team->load(['branch', 'leader', 'creator', 'activeMemberships.user.roles', 'activeMemberships.user.branch']);
        $historicalCount = $team->memberships()->whereNotNull('left_at')->count();
        return view('teams.show', compact('team', 'historicalCount'));
    }

    public function edit(CrmTeam $team): View
    {
        $team->load(['activeMemberships']);
        return view('teams.edit', ['team' => $team, ...$this->editorData($team)]);
    }

    public function update(Request $request, CrmTeam $team, CrmTeamMembershipManager $memberships): RedirectResponse
    {
        $data = $this->validated($request, $team);
        $ids = $data['member_user_ids'] ?? [];
        unset($data['member_user_ids']);
        DB::transaction(function () use ($team, $data, $ids, $memberships): void {
            $locked = CrmTeam::query()->whereKey($team->getKey())->lockForUpdate()->firstOrFail();
            $oldIds = $locked->activeMemberships()->pluck('user_id')->map(fn ($id) => (string) $id)->all();
            $old = ['team' => $locked->only(['team_code','team_name','team_type','status','branch_id','leader_user_id']), 'member_user_ids' => $oldIds];
            $memberships->validateSelection(
                $ids, $data['leader_user_id'] ?? null, $data['team_type'],
                $data['branch_id'] ?? null, $data['status'], $locked
            );
            $locked->update($data);
            $memberships->sync($locked, $ids);
            $new = ['team' => $locked->only(['team_code','team_name','team_type','status','branch_id','leader_user_id']), 'member_user_ids' => $ids];
            if ($old !== $new) $this->audit('UPDATE', $locked, $old, $new);
        });
        return redirect()->route('teams.show', $team)->with('success', 'Team updated successfully.');
    }

    public function destroy(CrmTeam $team): RedirectResponse
    {
        DB::transaction(function () use ($team): void {
            $locked = CrmTeam::query()->whereKey($team->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'archived') {
                throw ValidationException::withMessages(['team' => 'Archive the team before deleting it. Deletion preserves membership history.']);
            }
            $this->audit('DELETE', $locked, ['team_code' => $locked->team_code, 'status' => $locked->status], ['soft_deleted' => true]);
            $locked->delete(); // Soft delete: preserve reporting and membership history.
        });
        return redirect()->route('teams.index')->with('success', 'Archived team removed from the active directory.');
    }

    private function validated(Request $request, ?CrmTeam $team = null): array
    {
        $rawCode = $request->input('team_code');
        if (is_string($rawCode)) $request->merge(['team_code' => strtoupper(trim($rawCode))]);
        $data = $request->validate([
            'team_code' => [
                'required', 'string', 'max:32', 'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('crm_teams', 'team_code')->ignore($team?->getKey(), 'team_id'),
            ],
            'team_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'team_type' => ['required', Rule::in(['permanent', 'project'])],
            'status' => ['required', Rule::in(['active', 'archived'])],
            'branch_id' => ['nullable', 'uuid', 'exists:branches,branch_id', 'required_if:team_type,permanent'],
            'leader_user_id' => ['nullable', 'uuid', 'exists:users,user_id'],
            'starts_on' => ['nullable', 'date_format:Y-m-d', 'required_with:ends_on'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'required_with:starts_on', 'after_or_equal:starts_on'],
            'member_user_ids' => ['nullable', 'array', 'max:250'],
            'member_user_ids.*' => ['required', 'uuid', 'distinct', 'exists:users,user_id'],
        ]);
        if ($data['team_type'] === 'permanent') {
            $data['starts_on'] = null;
            $data['ends_on'] = null;
        }
        $data['branch_id'] = $data['branch_id'] ?? null;
        $data['leader_user_id'] = $data['leader_user_id'] ?? null;
        $data['description'] = $data['description'] ?? null;
        return $data;
    }

    private function editorData(?CrmTeam $team = null): array
    {
        $branches = Branch::query()->orderBy('branch_name')->get();
        $roles = Role::query()->whereRaw('LOWER(role_name) <> ?', ['super admin'])->orderBy('role_name')->get();
        $protectedRoleIds = Role::query()->where('is_system', true)
            ->whereRaw('LOWER(role_name) = ?', ['super admin'])->pluck('role_id');
        $users = User::query()->with(['roles', 'branch'])
            ->where(function ($q) use ($team): void {
                $q->whereRaw('LOWER(status) = ?', ['active']);
                if ($team) $q->orWhereIn('user_id', $team->activeMemberships()->select('user_id'));
            })
            ->whereNotIn('role_id', $protectedRoleIds)
            ->whereNotIn('user_id', DB::table('user_roles as ur')->join('roles as r', 'ur.role_id', '=', 'r.role_id')
                ->where('r.is_system', true)->whereRaw('LOWER(r.role_name) = ?', ['super admin'])->select('ur.user_id'))
            ->orderBy('name')->get();
        return compact('branches', 'roles', 'users');
    }

    private function audit(string $action, CrmTeam $team, ?array $old, array $new): void
    {
        DB::table('audit_logs')->insert([
            'audit_log_id' => (string) Str::uuid(),
            'user_id' => (string) Auth::id(),
            'action' => $action,
            'table_name' => 'crm_teams',
            'record_id' => (string) $team->getKey(),
            'old_value' => $old === null ? null : json_encode($old, JSON_THROW_ON_ERROR),
            'new_value' => json_encode($new, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
