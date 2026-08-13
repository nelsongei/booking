<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_account_id',
        'type',
        'points',
        'description',
        'reference_id',
    ];

    public function loyaltyAccount()
    {
        return $this->belongsTo(LoyaltyAccount::class);
    }
}
