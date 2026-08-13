<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeImage extends Model
{
    use HasFactory;

    protected $fillable = ['room_type_id', 'path', 'alt_text', 'is_primary', 'sort_order'];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
