<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'room_type_id', 'date', 'total',
        'blocked', 'sold', 'holds', 'protected', 'overbooking_allowed',
    ];

    protected $casts = [
        'date'                => 'date',
        'total'               => 'integer',
        'blocked'             => 'integer',
        'sold'                => 'integer',
        'holds'               => 'integer',
        'protected'           => 'integer',
        'overbooking_allowed' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Compute remaining available rooms: (total - blocked - sold - holds - protected) + overbooking
     */
    public function getAvailableAttribute(): int
    {
        $avail = ($this->total - $this->blocked - $this->sold - $this->holds - $this->protected) + $this->overbooking_allowed;
        return max(0, $avail);
    }
}
