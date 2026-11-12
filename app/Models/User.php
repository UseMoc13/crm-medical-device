<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'role_id',
        'branch_id',
        'name',
        'email',
        'password',
        'phone',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getAuthIdentifierName()
    {
        return 'user_id';
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /** Primary role compatibility for legacy CRM views: role(). */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id',
            'user_id',
            'role_id'
        )->withPivot(['user_role_id', 'is_primary', 'assigned_by', 'created_at']);
    }

    public function primaryRoleAssignment()
    {
        return $this->hasOne(UserRole::class, 'user_id', 'user_id')
            ->where('is_primary', true);
    }

    public function roleAssignments()
    {
        return $this->hasMany(UserRole::class, 'user_id', 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'user_id', 'user_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'user_id', 'user_id');
    }
}
