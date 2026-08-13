<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'code', 'name', 'category', 'revenue_category',
        'is_taxable', 'is_active',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
