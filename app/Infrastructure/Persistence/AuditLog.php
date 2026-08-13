<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'ulid', 'correlation_id', 'organization_id', 'property_id',
        'actor_user_id', 'actor_type', 'action',
        'target_type', 'target_id',
        'before', 'after', 'metadata',
        'source_ip', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'before'     => 'array',
        'after'      => 'array',
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
