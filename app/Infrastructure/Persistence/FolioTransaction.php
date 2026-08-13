<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolioTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'folio_account_id', 'folio_window_id', 'property_id', 'type',
        'charge_code_id', 'description', 'amount_minor', 'currency',
        'reverses_transaction_id', 'reversal_reason', 'posted_by',
        'posted_at', 'business_date', 'reference',
    ];

    protected $casts = [
        'posted_at'     => 'datetime',
        'business_date' => 'date',
        'amount_minor'  => 'integer',
    ];

    public function folioAccount()
    {
        return $this->belongsTo(FolioAccount::class);
    }

    public function window()
    {
        return $this->belongsTo(FolioWindow::class, 'folio_window_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function chargeCode()
    {
        return $this->belongsTo(ChargeCode::class);
    }

    public function reversesTransaction()
    {
        return $this->belongsTo(FolioTransaction::class, 'reverses_transaction_id');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
