<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'organization_id', 'property_id', 'code', 'name',
        'description', 'currency', 'meal_plan_id', 'cancellation_policy_id',
        'deposit_policy_id', 'is_public', 'is_refundable', 'breakfast_included',
        'min_advance_days', 'max_advance_days', 'channel_restrictions',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_public'          => 'boolean',
        'is_refundable'      => 'boolean',
        'breakfast_included' => 'boolean',
        'is_active'          => 'boolean',
        'min_advance_days'   => 'integer',
        'max_advance_days'   => 'integer',
        'channel_restrictions' => 'array',
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

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class);
    }

    public function cancellationPolicy()
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

    public function depositPolicy()
    {
        return $this->belongsTo(DepositPolicy::class);
    }

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'rate_plan_room_types');
    }

    public function rateDays()
    {
        return $this->hasMany(RateDay::class);
    }

    public function rateRestrictions()
    {
        return $this->hasMany(RateRestriction::class);
    }
}
