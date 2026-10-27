<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Contract extends Model
{
    use HasUuids;

    protected $table = 'contracts';

    protected $primaryKey = 'contract_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sales_order_id',
        'contract_number',
        'start_date',
        'end_date',
        'contract_type',
        'status',
        'document_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(
            SalesOrder::class,
            'sales_order_id',
            'sales_order_id'
        );
    }
}