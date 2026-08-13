<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'name', 'slug', 'legal_name', 'tax_identifier',
        'default_currency', 'default_timezone', 'default_locale',
        'status', 'settings', 'address', 'country', 'phone', 'email', 'website',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($org) {
            if (empty($org->slug) && !empty($org->name)) {
                $org->slug = \Illuminate\Support\Str::slug($org->name);
            }
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
