<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\CrmRoleAccessCatalog as Catalog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::query()
            ->withCount('users');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'role_name',
                    'ILIKE',
                    "%{$search}%"
                )

                    ->orWhere(
                        'description',
                        'ILIKE',
                        "%{$search}%"
                    );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'role_name',
            'created_at',
            'updated_at',
        ];

        $sort = $request->get(
            'sort',
            'created_at'
        );

        $direction = $request->get(
            'direction',
            'desc'
        );


        if (!in_array($sort, $allowedSorts)) {

            $sort = 'created_at';
        }


        if (!in_array($direction, ['asc', 'desc'])) {

            $direction = 'desc';
        }


        $query->orderBy(
            $sort,
            $direction
        );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $roles = $query
            ->paginate(10)
            ->withQueryString();


        return view(
            'roles.index',
            compact(
                'roles',
                'sort',
                'direction'
            )
        );
    }


    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('roles.create', $this->editorData(null));
    }


    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'role_name' => [
                'required',
                'string',
                'max:50',
                'unique:roles,role_name',
            ],

            'description' => [
                'nullable',
                'string',
            ],

        ]);


        if (strcasecmp(trim($validated['role_name']), 'Super Admin') === 0) {
            abort(403, 'The protected Super Admin role can only be provisioned using the authorized bootstrap process.');
        }

        $role = DB::transaction(function () use ($validated, $request) {
            $role = Role::create($validated);
            if (! $role->role_id) $role = Role::where('role_name', $validated['role_name'])->firstOrFail();
            $this->syncPermissions($role, $this->requestedAccess($request, $validated['role_name']));
            return $role;
        });


        return redirect()
            ->route('roles.index')
            ->with(
                'success',
                'Role berhasil ditambahkan.'
            );
    }


    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        return view(
            'roles.show',
            array_merge(compact('role'), $this->editorData($role))
        );
    }


    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $this->assertNotProtected($role);
        return view(
            'roles.edit',
            array_merge(compact('role'), $this->editorData($role))
        );
    }


    /**
     * Update the specified role.
     */
    public function update(
        Request $request,
        Role $role
    ) {
        $this->assertNotProtected($role);

        $validated = $request->validate([

            'role_name' => [
                'required',
                'string',
                'max:50',
                'unique:roles,role_name,' .
                    $role->role_id .
                    ',role_id',
            ],

            'description' => [
                'nullable',
                'string',
            ],

        ]);


        if (strcasecmp(trim($validated['role_name']), 'Super Admin') === 0) {
            abort(403, 'Super Admin is a reserved role name.');
        }
        DB::transaction(function () use ($role, $validated, $request) {
            $role->update($validated);
            $this->syncPermissions($role, $this->requestedAccess($request, $validated['role_name']));
        });


        return redirect()
            ->route(
                'roles.show',
                $role
            )
            ->with(
                'success',
                'Role berhasil diperbarui.'
            );
    }


    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        $this->assertNotProtected($role);
        /*
        |--------------------------------------------------------------------------
        | Prevent deleting a role that is still being used
        |--------------------------------------------------------------------------
        */

        if ($role->users()->exists()) {

            return redirect()
                ->route('roles.index')
                ->with(
                    'error',
                    'Role tidak dapat dihapus karena masih digunakan oleh user.'
                );
        }


        $role->delete();


        return redirect()
            ->route('roles.index')
            ->with(
                'success',
                'Role berhasil dihapus.'
            );
    }
    private function editorData(?Role $role): array
    {
        $selected = [];
        if ($role) {
            $keys = DB::table('role_permissions as rp')
                ->join('permissions as p', 'p.permission_id', '=', 'rp.permission_id')
                ->where('rp.role_id', $role->role_id)
                ->where('p.module', 'crm_page')
                ->pluck('p.permission_name')->all();
            foreach (Catalog::PAGES as $slug => $config) {
                foreach (Catalog::ACTIONS as $action) {
                    if (in_array(Catalog::key($slug, $action), $keys, true)) $selected[$slug][] = $action;
                }
            }
        }
        return ['accessPages'=>Catalog::PAGES, 'accessActions'=>Catalog::ACTIONS,
            'accessSelected'=>$selected, 'accessTemplates'=>['admin'=>'Admin','sales'=>'Sales','warehouse'=>'Warehouse','delivery_staff'=>'Delivery Staff','technician'=>'Technician','finance'=>'Finance','management'=>'Management'],
            'defaultTemplate'=>Catalog::templateForRole($role?->role_name ?? ''),
            'templateOptions'=>collect(['admin','sales','warehouse','delivery_staff','technician','finance','management'])->mapWithKeys(fn ($key) => [$key => Catalog::template($key)])->all()];
    }

    private function requestedAccess(Request $request, string $name): array
    {
        $input = $request->validate([
            'access_mode' => ['required', 'in:custom,template'],
            'access_template' => ['nullable', 'in:admin,sales,warehouse,delivery_staff,technician,finance,management'],
            'page_access' => ['nullable', 'array'],
            'page_access.*' => ['nullable', 'array'],
            'page_access.*.*' => ['string', 'in:view,create,update,delete'],
        ]);
        if ($input['access_mode'] === 'template') {
            if (empty($input['access_template'])) throw \Illuminate\Validation\ValidationException::withMessages(['access_template'=>'Select a CRM role template.']);
            return Catalog::template($input['access_template']);
        }
        $chosen = $input['page_access'] ?? [];
        foreach ($chosen as $slug => $actions) {
            if (! isset(Catalog::PAGES[$slug])) throw \Illuminate\Validation\ValidationException::withMessages(['page_access'=>'Unknown page selection.']);
        }
        $out=[];
        foreach (Catalog::PAGES as $slug => $page) {
            $actions = array_values(array_unique($chosen[$slug] ?? []));
            if ($actions) $out[$slug] = $actions;
        }
        return $out;
    }

    private function syncPermissions(Role $role, array $access): void
    {
        if (! DB::table('permissions')->where('permission_name', Catalog::key('customers','view'))->exists()) {
            throw new \RuntimeException('A6.2 migration has not been run. Run php artisan migrate before editing roles.');
        }
        // A6.2 only manages crm_page and A6.1 entrance grants. Any other
        // permissions are intentionally preserved for a later dedicated editor.
        $keys = [];
        $base = [];
        foreach ($access as $slug => $actions) {
            if (! isset(Catalog::PAGES[$slug])) continue;
            foreach ($actions as $action) $keys[] = Catalog::key($slug, $action);
            if ($actions) {
                $base[] = Catalog::PAGES[$slug]['base'];
                if ($slug === 'products' || $slug === 'product_categories' || $slug === 'brands') {
                    if (array_intersect($actions, ['create', 'update', 'delete'])) $base[] = 'catalog.manage';
                }
            }
        }
        $keys = array_unique(array_merge($keys, $base));
        $managed = array_merge(
            DB::table('permissions')->where('module', 'crm_page')->pluck('permission_id')->all(),
            DB::table('permissions')->whereIn('permission_name', [
                'customer_lead.access','sales_crm.access','catalog.access','catalog.manage','inventory.access',
                'delivery.access','installation.access','service.access','billing.access','reporting.access',
                'announcements.manage',
            ])->pluck('permission_id')->all()
        );
        $desired = DB::table('permissions')->whereIn('permission_name', $keys)->pluck('permission_id')->all();
        DB::table('role_permissions')->where('role_id', $role->role_id)->whereIn('permission_id', $managed)->delete();
        foreach ($desired as $pid) DB::table('role_permissions')->insert([
            'role_permission_id'=>(string) Str::uuid(), 'role_id'=>$role->role_id,
            'permission_id'=>$pid, 'data_scope'=>null, 'created_at'=>now(),
        ]);
        // This change is security-sensitive, always audited.
        DB::table('audit_logs')->insert([
            'audit_log_id'=>(string) Str::uuid(), 'user_id'=>Auth::id(),
            'action'=>'UPDATE', 'table_name'=>'role_permissions', 'record_id'=>$role->role_id,
            'old_value'=>null, 'new_value'=>json_encode(['page_access'=>$access]), 'created_at'=>now(),
        ]);
    }

    private function assertNotProtected(Role $role): void
    {
        if ($role->is_system || strcasecmp((string) $role->role_name, 'Super Admin') === 0) {
            abort(403, 'Protected system roles cannot be edited or deleted.');
        }
    }
}
