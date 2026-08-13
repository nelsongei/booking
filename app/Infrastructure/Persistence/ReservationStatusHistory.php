<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'reservation_status_history';

    protected $fillable = [
        'reservation_id', 'from_status', 'to_status', 'reason', 'changed_by', 'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
