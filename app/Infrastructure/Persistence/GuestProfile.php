<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'organization_id', 'first_name', 'last_name',
        'email', 'phone', 'nationality', 'language', 'id_type',
        'id_number', 'date_of_birth', 'gender', 'title',
        'preferences', 'notes', 'total_stays', 'total_nights',
    ];

    protected $casts = [
        'preferences'   => 'array',
        'date_of_birth' => 'date',
        'total_stays'   => 'integer',
        'total_nights'  => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'primary_guest_id');
    }

    public function loyaltyAccount()
    {
        return $this->hasOne(LoyaltyAccount::class, 'guest_profile_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->title ? $this->title . ' ' : '') . $this->first_name . ' ' . $this->last_name);
    }
}
