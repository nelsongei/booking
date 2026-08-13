<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationNote extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'user_id', 'type', 'is_alert', 'content'];

    protected $casts = [
        'is_alert' => 'boolean',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
