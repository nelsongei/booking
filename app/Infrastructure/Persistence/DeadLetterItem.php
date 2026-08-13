<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeadLetterItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'reason',
        'payload',
        'attempts',
        'status',
        'notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'resolved_at' => 'datetime',
    ];

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
