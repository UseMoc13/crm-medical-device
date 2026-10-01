<?php

namespace App\Http\Controllers;

use App\Models\Role;
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
        return view('roles.create');
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


        Role::create($validated);


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
            compact('role')
        );
    }


    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        return view(
            'roles.edit',
            compact('role')
        );
    }


    /**
     * Update the specified role.
     */
    public function update(
        Request $request,
        Role $role
    ) {

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


        $role->update(
            $validated
        );


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
}
