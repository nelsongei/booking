<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolioAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'reservation_id', 'stay_id', 'property_id', 'type', 'status', 'currency',
        'company_id', 'travel_agent_id',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function windows()
    {
        return $this->hasMany(FolioWindow::class);
    }

    public function transactions()
    {
        return $this->hasMany(FolioTransaction::class);
    }

    /**
     * Compute dynamic net folio balance (sum of all transactions).
     */
    public function getBalanceMinorAttribute(): int
    {
        return (int) $this->transactions()->sum('amount_minor');
    }
}
