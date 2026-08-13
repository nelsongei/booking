<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryHold extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'property_id', 'room_type_id', 'reservation_ulid',
        'check_in', 'check_out', 'rooms_count', 'status', 'source',
        'session_token', 'expires_at',
    ];

    protected $casts = [
        'check_in'    => 'date',
        'check_out'   => 'date',
        'rooms_count' => 'integer',
        'expires_at'  => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
