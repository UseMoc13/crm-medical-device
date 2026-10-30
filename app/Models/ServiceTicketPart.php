<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTicketPart extends Model
{
    protected $table = 'service_ticket_parts';

    protected $primaryKey = 'service_ticket_part_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'service_ticket_id',
        'product_id',
        'quantity',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function serviceTicket(): BelongsTo
    {
        return $this->belongsTo(
            ServiceTicket::class,
            'service_ticket_id',
            'service_ticket_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}