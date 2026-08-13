<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NightAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'business_date',
        'status',
        'steps',
        'started_by',
        'completed_by',
        'started_at',
        'completed_at',
        'failure_notes',
        'report_data',
    ];

    protected $casts = [
        'business_date' => 'date',
        'steps'         => 'array',
        'report_data'   => 'array',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
