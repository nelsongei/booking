<?php

namespace App\Domain\Pos\Models;

use App\Infrastructure\Persistence\FolioAccount;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PosOrder extends Model
{
    protected $table = 'pos_orders';

    protected $fillable = [
        'ulid', 'property_id', 'pos_outlet_id', 'reservation_id',
        'folio_account_id', 'server_user_id', 'order_type',
        'table_number', 'covers', 'status', 'payment_status',
        'payment_method', 'subtotal_minor', 'tax_minor', 'total_minor',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function outlet()
    {
        return $this->belongsTo(PosOutlet::class, 'pos_outlet_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function folioAccount()
    {
        return $this->belongsTo(FolioAccount::class);
    }

    public function server()
    {
        return $this->belongsTo(User::class, 'server_user_id');
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->ulid)) {
                $order->ulid = (string) Str::ulid();
            }
        });
    }
}
