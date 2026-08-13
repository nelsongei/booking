<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'reservation_id', 'room_type_id', 'rate_plan_id',
        'adults', 'children', 'child_ages', 'status',
        'subtotal_minor', 'tax_minor', 'total_minor',
        'rate_snapshot', 'policy_snapshot',
    ];

    protected $casts = [
        'adults'          => 'integer',
        'children'        => 'integer',
        'child_ages'      => 'array',
        'subtotal_minor'  => 'integer',
        'tax_minor'       => 'integer',
        'total_minor'     => 'integer',
        'rate_snapshot'   => 'array',
        'policy_snapshot' => 'array',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function nights()
    {
        return $this->hasMany(ReservationNight::class);
    }
}
