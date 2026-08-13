<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'property_id', 'name', 'code',
        'description', 'rules', 'is_non_refundable',
    ];

    protected $casts = [
        'rules'             => 'array',
        'is_non_refundable' => 'boolean',
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
