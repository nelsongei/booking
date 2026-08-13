<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolioWindow extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio_account_id', 'name', 'window_number', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function folioAccount()
    {
        return $this->belongsTo(FolioAccount::class);
    }

    public function transactions()
    {
        return $this->hasMany(FolioTransaction::class);
    }
}
