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
            if (empty($room->room_number)) {
                $room->room_number = $room->number ?? (string) rand(100, 999);
            }
            if (empty($room->ulid)) {
                $room->ulid = (string) \Illuminate\Support\Str::ulid();
            }
        });
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
