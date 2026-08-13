<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'organization_id',
        'property_id',
        'provider',
        'status',
        'credentials_encrypted',
        'settings',
        'last_sync_at',
    ];

    protected $casts = [
        'credentials_encrypted' => 'array',
        'settings'              => 'array',
        'last_sync_at'          => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
