<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceTicket extends Model
{
    protected $table = 'service_tickets';

    protected $primaryKey = 'service_ticket_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'serial_number_id',
        'user_id',
        'ticket_number',
        'title',
        'description',
        'priority',
        'status',
        'opened_at',
        'closed_at',
        'resolution',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(SerialNumber::class, 'serial_number_id', 'serial_number_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(
            ServiceTicketPart::class,
            'service_ticket_id',
            'service_ticket_id'
        );
    }
}