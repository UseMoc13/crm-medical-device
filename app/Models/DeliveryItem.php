<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryItem extends Model
{
    protected $table = 'delivery_items';
    protected $primaryKey = 'delivery_item_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['delivery_item_id', 'delivery_id', 'serial_number_id', 'warehouse_id', 'quantity'];

    protected static function booted(): void
    {
        static::creating(function ($item) {
            if (!$item->delivery_item_id) $item->delivery_item_id = (string) Str::uuid();
        });
    }

    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class, 'serial_number_id', 'serial_number_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'warehouse_id');
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'delivery_id');
    }
}
