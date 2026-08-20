<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pos\Models\PosOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PosOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $query = PosOrder::where('property_id', $property->id)
            ->with(['outlet', 'reservation.primaryGuest', 'server']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.pos.orders', compact('property', 'orders'));
    }
}
