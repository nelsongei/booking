<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'confirmation_number', 'organization_id', 'property_id',
        'primary_guest_id', 'booking_source_id', 'company_id', 'travel_agent_id',
        'rate_plan_id', 'status', 'check_in', 'check_out', 'nights',
        'rooms_count', 'adults', 'children', 'currency', 'subtotal_minor',
        'tax_minor', 'fee_minor', 'discount_minor', 'total_minor',
        'deposit_minor', 'balance_minor', 'promo_code', 'policy_snapshot',
        'special_requests', 'booking_channel', 'source_reference', 'created_by',
        'confirmed_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected $casts = [
        'check_in'        => 'date',
        'check_out'       => 'date',
        'nights'          => 'integer',
        'rooms_count'     => 'integer',
        'adults'          => 'integer',
        'children'        => 'integer',
        'subtotal_minor'  => 'integer',
        'tax_minor'       => 'integer',
        'fee_minor'       => 'integer',
        'discount_minor'  => 'integer',
        'total_minor'     => 'integer',
        'deposit_minor'   => 'integer',
        'balance_minor'   => 'integer',
        'policy_snapshot' => 'array',
        'confirmed_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function primaryGuest()
    {
        return $this->belongsTo(GuestProfile::class, 'primary_guest_id');
    }

    public function bookingSource()
    {
        return $this->belongsTo(BookingSource::class);
    }

    public function ratePlan()
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function rooms()
    {
        return $this->hasMany(ReservationRoom::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(ReservationStatusHistory::class)->orderBy('changed_at', 'desc');
    }

    public function notes()
    {
        return $this->hasMany(ReservationNote::class)->orderBy('created_at', 'desc');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stays()
    {
        return $this->hasMany(Stay::class);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed' || $this->status === 'checked_in';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
