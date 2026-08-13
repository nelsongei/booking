<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class PropertySetting extends Model
{
    protected $fillable = ['property_id', 'key', 'value', 'type'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getValueAttribute($value): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}
