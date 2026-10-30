<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialNumber extends Model
{
    protected $table = 'serial_numbers';

    protected $primaryKey = 'serial_number_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'customer_id',
        'serial_number',
        'status',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
            'product_id'
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id',
            'customer_id'
        );
    }
}
