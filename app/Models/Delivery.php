<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Delivery extends Model
{
    protected $table = 'deliveries';

    protected $primaryKey = 'delivery_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'delivery_id',
        'sales_order_id',
        'delivery_number',
        'delivery_date',
        'delivery_address',
        'status',
        'notes',
    ];

    public static function generateDeliveryNumber(): string
{
    $year = now()->format('Y');
    $prefix = 'DEL-' . $year . '-';

    $lastDelivery = self::where(
        'delivery_number',
        'like',
        $prefix . '%'
    )
        ->orderByDesc('delivery_number')
        ->first();

    $nextNumber = $lastDelivery
        ? ((int) substr($lastDelivery->delivery_number, -3)) + 1
        : 1;

    return $prefix . str_pad(
        $nextNumber,
        3,
        '0',
        STR_PAD_LEFT
    );
}

    protected $casts = [
        'delivery_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($delivery) {
            if (empty($delivery->delivery_id)) {
                $delivery->delivery_id = (string) Str::uuid();
            }
        });
    }

    public function salesOrder()
    {
        return $this->belongsTo(
            SalesOrder::class,
            'sales_order_id',
            'sales_order_id'
        );
    }
}