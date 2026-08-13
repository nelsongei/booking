<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'rate_plan_id', 'room_type_id', 'date',
        'amount_minor', 'currency', 'extra_adult_minor', 'extra_child_minor',
    ];

    protected $casts = [
        'date'               => 'date',
        'amount_minor'       => 'integer',
        'extra_adult_minor'  => 'integer',
        'extra_child_minor'  => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
