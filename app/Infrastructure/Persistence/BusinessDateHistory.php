<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessDateHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'business_date_history';

    protected $fillable = [
        'property_id',
        'business_date',
        'previous_business_date',
        'rolled_by',
        'rolled_at',
    ];

    protected $casts = [
        'business_date'          => 'date',
        'previous_business_date' => 'date',
        'rolled_at'              => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function rolledBy()
    {
        return $this->belongsTo(User::class, 'rolled_by');
    }
}
