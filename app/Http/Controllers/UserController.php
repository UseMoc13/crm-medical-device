<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->with([
                'role',
                'branch',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'ILIKE',
                    "%{$search}%"
                )

                ->orWhere(
                    'email',
                    'ILIKE',
                    "%{$search}%"
                )

                ->orWhere(
                    'phone',
                    'ILIKE',
                    "%{$search}%"
                );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | Filter by Role
        |--------------------------------------------------------------------------
        */

        if ($request->filled('role_id')) {

            $query->where(
                'role_id',
                $request->role_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Filter by Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'name',
            'email',
            'status',
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

        $users = $query
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $roles = Role::query()
            ->orderBy('role_name')
            ->get([
                'role_id',
                'role_name',
            ]);


        return view(
            'users.index',
            compact(
                'users',
                'roles',
                'sort',
                'direction'
            )
        );
    }


    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::query()
            ->orderBy('role_name')
            ->get([
                'role_id',
                'role_name',
            ]);


        $branches = Branch::query()
            ->orderBy('branch_name')
            ->get([
                'branch_id',
                'branch_name',
            ]);


        return view(
            'users.create',
            compact(
                'roles',
                'branches'
            )
        );
    }


    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'role_id' => [
                'required',
                'uuid',
                'exists:roles,role_id',
            ],

            'branch_id' => [
                'nullable',
                'uuid',
                'exists:branches,branch_id',
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

        ]);


        User::create($validated);


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil ditambahkan.'
            );
    }


    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load([
            'role',
            'branch',
        ]);


        return view(
            'users.show',
            compact('user')
        );
    }


    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::query()
            ->orderBy('role_name')
            ->get([
                'role_id',
                'role_name',
            ]);


        $branches = Branch::query()
            ->orderBy('branch_name')
            ->get([
                'branch_id',
                'branch_name',
            ]);


        return view(
            'users.edit',
            compact(
                'user',
                'roles',
                'branches'
            )
        );
    }


    /**
     * Update the specified user.
     */
    public function update(
        Request $request,
        User $user
    ) {

        $validated = $request->validate([

            'role_id' => [
                'required',
                'uuid',
                'exists:roles,role_id',
            ],

            'branch_id' => [
                'nullable',
                'uuid',
                'exists:branches,branch_id',
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore(
                        $user->user_id,
                        'user_id'
                    ),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        |
        | Password hanya diubah jika user memasukkan password baru.
        |
        */

        if (empty($validated['password'])) {

            unset($validated['password']);
        }


        $user->update(
            $validated
        );


        return redirect()
            ->route(
                'users.show',
                $user
            )
            ->with(
                'success',
                'User berhasil diperbarui.'
            );
    }


    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent deleting user that is still referenced
        |--------------------------------------------------------------------------
        */

        if (
            $user->customers()->exists()
            ||
            $user->leads()->exists()
        ) {

            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'User tidak dapat dihapus karena masih digunakan oleh data CRM.'
                );
        }


        $user->delete();


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil dihapus.'
            );
    }
}