<?php

namespace App\Domain\Pos\Models;

use App\Infrastructure\Persistence\Property;
use Illuminate\Database\Eloquent\Model;

class PosOutlet extends Model
{
    protected $table = 'pos_outlets';

    protected $fillable = ['property_id', 'name', 'code', 'type', 'is_active'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
