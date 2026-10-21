<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';
    protected $primaryKey = 'sales_order_id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sales_order_id',
        'quotation_id',
        'sales_order_number',
        'order_date',
        'delivery_date',
        'subtotal',
        'tax',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->sales_order_id)) {
                $order->sales_order_id = (string) Str::uuid();
            }
        });
    }

    public function quotation()
    {
        return $this->belongsTo(
            Quotation::class,
            'quotation_id',
            'quotation_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            SalesOrderItem::class,
            'sales_order_id',
            'sales_order_id'
        );
    }
}
