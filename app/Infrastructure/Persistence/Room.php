<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'property_id', 'room_type_id', 'building_id', 'floor_id',
        'room_number', 'name', 'status', 'is_smoking', 'features', 'notes',
    ];

    protected $casts = [
        'is_smoking' => 'boolean',
        'features'   => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($room) {
            $num = $room->attributes['room_number'] ?? ($room->attributes['number'] ?? null);
            if (empty($num)) {
                $num = (string) rand(100, 999);
            }
            $room->room_number = (string) $num;

            if (empty($room->ulid)) {
                $room->ulid = (string) \Illuminate\Support\Str::ulid();
            }
        });
    }

    public function getNumberAttribute()
    {
        return $this->attributes['room_number'] ?? null;
    }

    public function setNumberAttribute($value)
    {
        $this->attributes['room_number'] = $value;
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function stays()
    {
        return $this->hasMany(Stay::class);
    }

    public function isHousekeepingClean(): bool
    {
        return $this->status === 'clean' || $this->status === 'inspected';
    }

    public function isAvailable(): bool
    {
        return !in_array($this->status, ['out_of_order', 'out_of_service']);
    }
}
