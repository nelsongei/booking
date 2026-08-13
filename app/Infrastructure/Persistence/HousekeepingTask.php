<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'room_id',
        'assigned_to',
        'assigned_by',
        'type',
        'status',
        'priority',
        'due_date',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'started_at'   => 'datetime',
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
