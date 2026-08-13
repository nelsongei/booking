<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'building_id', 'name', 'level'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
