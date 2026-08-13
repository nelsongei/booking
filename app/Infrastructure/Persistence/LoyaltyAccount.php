<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_profile_id',
        'account_number',
        'tier',
        'points_balance',
        'lifetime_points',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function guestProfile()
    {
        return $this->belongsTo(GuestProfile::class, 'guest_profile_id');
    }

    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class)->latest();
    }
}
