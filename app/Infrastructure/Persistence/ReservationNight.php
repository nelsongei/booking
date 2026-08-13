<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationNight extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_room_id', 'date', 'rate_minor',
        'tax_minor', 'total_minor', 'currency', 'breakdown',
    ];

    protected $casts = [
        'date'        => 'date',
        'rate_minor'  => 'integer',
        'tax_minor'   => 'integer',
        'total_minor' => 'integer',
        'breakdown'   => 'array',
    ];

    public function reservationRoom()
    {
        return $this->belongsTo(ReservationRoom::class);
    }
}
