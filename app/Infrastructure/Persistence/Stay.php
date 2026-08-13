<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stay extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'reservation_id', 'reservation_room_id', 'property_id',
        'room_id', 'status', 'arrival_date', 'departure_date',
        'checked_in_at', 'checked_out_at', 'checked_in_by', 'checked_out_by',
    ];

    protected $casts = [
        'arrival_date'   => 'date',
        'departure_date' => 'date',
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reservationRoom()
    {
        return $this->belongsTo(ReservationRoom::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function checkinRecord()
    {
        return $this->hasOne(CheckinRecord::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }
}
