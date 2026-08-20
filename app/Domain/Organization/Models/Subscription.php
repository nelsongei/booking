<?php

namespace App\Domain\Organization\Models;

use App\Infrastructure\Persistence\Organization;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'saas_subscriptions';

    protected $fillable = [
        'organization_id', 'plan_tier', 'status',
        'max_properties', 'max_rooms_per_property',
        'features_enabled', 'current_period_start', 'current_period_end',
    ];

    protected $casts = [
        'features_enabled'     => 'array',
        'current_period_start' => 'date',
        'current_period_end'   => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function hasFeature(string $featureKey): bool
    {
        if ($this->plan_tier === 'enterprise') {
            return true;
        }

        $features = $this->features_enabled ?? [];
        return in_array($featureKey, $features, true);
    }
}
