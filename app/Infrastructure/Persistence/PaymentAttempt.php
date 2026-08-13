<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'provider_session_id',
        'status',
        'raw_response',
        'attempted_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'attempted_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
