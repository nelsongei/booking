<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'property_id', 'input', 'output', 'trace',
        'promo_code', 'currency', 'total_minor', 'expires_at',
    ];

    protected $casts = [
        'input'       => 'array',
        'output'      => 'array',
        'trace'       => 'array',
        'total_minor' => 'integer',
        'expires_at'  => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
