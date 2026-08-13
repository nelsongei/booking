<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSource extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'type', 'channel', 'commission_percent', 'is_active'];

    protected $casts = [
        'commission_percent' => 'decimal:2',
        'is_active'          => 'boolean',
    ];
}
