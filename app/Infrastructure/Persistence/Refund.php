<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'payment_id',
        'reservation_id',
        'property_id',
        'provider_refund_id',
        'amount_minor',
        'currency',
        'reason',
        'status',
        'idempotency_key',
        'approved_by',
        'processed_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
