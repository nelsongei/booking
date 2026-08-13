<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class PropertyUserAssignment extends Model
{
    protected $fillable = ['user_id', 'property_id', 'organization_id', 'role_name', 'is_active', 'expires_at'];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isEffective(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}
