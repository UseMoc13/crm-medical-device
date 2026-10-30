<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;

    protected $table = 'warranties';

    protected $primaryKey = 'warranty_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'serial_number_id',
        'customer_id',
        'warranty_number',
        'start_date',
        'end_date',
        'warranty_type',
        'status',
        'terms',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function serialNumber()
    {
        return $this->belongsTo(
            SerialNumber::class,
            'serial_number_id',
            'serial_number_id'
        );
    }

    public function customer()
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id',
            'customer_id'
        );
    }
}