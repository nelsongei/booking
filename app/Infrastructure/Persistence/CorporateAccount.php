<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'company_name',
        'code',
        'tax_id',
        'credit_limit_minor',
        'contact_name',
        'contact_email',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function groupAllotments()
    {
        return $this->hasMany(GroupAllotment::class);
    }
}
