<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinRecord extends Model
{
    use HasFactory;

    protected $table = 'checkin_records';

    protected $fillable = [
        'stay_id', 'id_type', 'id_number', 'id_country', 'id_expiry',
        'guest_signature_path', 'additional_guests', 'notes',
    ];

    protected $casts = [
        'id_expiry'         => 'date',
        'additional_guests' => 'array',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }
}
