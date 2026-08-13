<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'property_id', 'name', 'code', 'type',
        'amount_minor', 'currency', 'percentage', 'collection_timing',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'percentage'   => 'decimal:2',
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
