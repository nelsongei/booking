<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'organization_id', 'property_id', 'code', 'name',
        'description', 'bed_type', 'base_occupancy', 'max_adults',
        'max_children', 'max_occupancy', 'size_sqm', 'view',
        'is_accessible', 'smoking_allowed', 'status', 'sort_order',
    ];

    protected $casts = [
        'is_accessible'   => 'boolean',
        'smoking_allowed' => 'boolean',
        'base_occupancy'  => 'integer',
        'max_adults'      => 'integer',
        'max_children'    => 'integer',
        'max_occupancy'   => 'integer',
        'size_sqm'        => 'integer',
        'sort_order'      => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->organization_id) && !empty($model->property_id)) {
                $model->organization_id = Property::find($model->property_id)?->organization_id;
            }
        });
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_type_amenities');
    }

    public function images()
    {
        return $this->hasMany(RoomTypeImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(RoomTypeImage::class)->where('is_primary', true);
    }

    public function ratePlans()
    {
        return $this->belongsToMany(RatePlan::class, 'rate_plan_room_types');
    }

    public function rateDays()
    {
        return $this->hasMany(RateDay::class);
    }
}
