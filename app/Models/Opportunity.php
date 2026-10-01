<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    protected $table = 'opportunities';

    protected $primaryKey = 'opportunity_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'customer_id',
        'lead_id',
        'user_id',
        'opportunity_code',
        'name',
        'description',
        'stage',
        'estimated_value',
        'expected_close_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id',
            'customer_id'
        );
    }

    public function lead()
    {
        return $this->belongsTo(
            Lead::class,
            'lead_id',
            'lead_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            OpportunityItem::class,
            'opportunity_id',
            'opportunity_id'
        );
    }
}