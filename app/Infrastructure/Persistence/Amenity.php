<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'category', 'icon'];

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_type_amenities');
    }
}
