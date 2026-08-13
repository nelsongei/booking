<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'property_id', 'name', 'code', 'type',
        'rate', 'currency', 'is_included_in_rate', 'applies_to_extras',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'rate'                 => 'decimal:4',
        'is_included_in_rate' => 'boolean',
        'applies_to_extras'    => 'boolean',
        'is_active'            => 'boolean',
        'sort_order'           => 'integer',
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
