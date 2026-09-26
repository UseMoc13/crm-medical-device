<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $primaryKey = 'permission_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'permission_name',
        'module',
        'action',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function rolePermissions()
    {
        return $this->hasMany(
            RolePermission::class,
            'permission_id',
            'permission_id'
        );
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id',
            'permission_id',
            'role_id'
        );
    }
}