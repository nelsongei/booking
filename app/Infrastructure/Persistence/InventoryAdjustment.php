<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'room_type_id', 'date', 'type',
        'quantity', 'reason', 'created_by',
    ];

    protected $casts = [
        'date'     => 'date',
        'quantity' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
