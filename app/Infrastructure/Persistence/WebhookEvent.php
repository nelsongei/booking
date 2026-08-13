<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'provider',
        'event_type',
        'provider_event_id',
        'payload',
        'status',
        'attempts',
        'failure_reason',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'processed_at' => 'datetime',
    ];
}
