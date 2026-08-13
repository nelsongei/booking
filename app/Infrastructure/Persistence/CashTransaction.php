<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_shift_id', 'payment_id', 'type', 'amount_minor',
        'currency', 'reference', 'transacted_at',
    ];

    protected $casts = [
        'transacted_at' => 'datetime',
    ];

    public function cashierShift()
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
