<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'reservation_id',
        'folio_account_id',
        'property_id',
        'invoice_number',
        'type',
        'line_items',
        'subtotal_minor',
        'tax_minor',
        'total_minor',
        'currency',
        'pdf_path',
        'issued_at',
    ];

    protected $casts = [
        'line_items' => 'array',
        'issued_at'  => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function folioAccount()
    {
        return $this->belongsTo(FolioAccount::class);
    }
}
