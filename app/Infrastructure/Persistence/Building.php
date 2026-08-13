<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'name', 'code'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
