<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAllotment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'corporate_account_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'rooms_allocated',
        'rooms_picked_up',
        'negotiated_rate_minor',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function corporateAccount()
    {
        return $this->belongsTo(CorporateAccount::class);
    }
}
