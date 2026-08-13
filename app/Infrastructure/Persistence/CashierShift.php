<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'user_id', 'status', 'opening_balance_minor',
        'closing_balance_minor', 'expected_closing_minor', 'variance_minor',
        'notes', 'opened_at', 'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }
}
