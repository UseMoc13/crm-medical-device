<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $primaryKey = 'customer_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'customer_code',
        'customer_name',
        'customer_type',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }

    public function contacts()
    {
        return $this->hasMany(
            Contact::class,
            'customer_id',
            'customer_id'
        );
    }

    public function leads()
    {
        return $this->hasMany(
            Lead::class,
            'customer_id',
            'customer_id'
        );
    }

    public function activities()
    {
        return $this->hasMany(
            Activity::class,
            'customer_id',
            'customer_id'
        );
    }
}