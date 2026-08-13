<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'room_id',
        'category',
        'priority',
        'status',
        'description',
        'reported_by',
        'assigned_to',
        'completed_at',
        'resolution_notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
