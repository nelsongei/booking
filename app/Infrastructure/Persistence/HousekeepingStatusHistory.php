<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'housekeeping_status_history';

    protected $fillable = [
        'room_id',
        'property_id',
        'from_status',
        'to_status',
        'changed_by',
        'source',
        'notes',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
