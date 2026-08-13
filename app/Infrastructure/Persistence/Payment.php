<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'reservation_id',
        'folio_account_id',
        'property_id',
        'provider',
        'provider_payment_id',
        'provider_customer_id',
        'provider_payment_method',
        'amount_minor',
        'currency',
        'status',
        'type',
        'provider_metadata',
        'idempotency_key',
        'authorized_at',
        'captured_at',
        'failed_at',
        'failure_reason',
        'processed_by',
    ];

    protected $casts = [
        'provider_metadata' => 'array',
        'authorized_at'     => 'datetime',
        'captured_at'       => 'datetime',
        'failed_at'         => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function folioAccount()
    {
        return $this->belongsTo(FolioAccount::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
