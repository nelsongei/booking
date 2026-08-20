<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pos\Models\PosOrder;
use App\Domain\Pos\Models\PosOutlet;
use App\Domain\Pos\Services\PosPostingService;
use App\Domain\Pos\Services\StockControlService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PosTerminalController extends Controller
{
    protected PosPostingService $postingService;
    protected StockControlService $stockService;

    public function __construct(PosPostingService $postingService, StockControlService $stockService)
    {
        $this->middleware('auth');
        $this->postingService = $postingService;
        $this->stockService   = $stockService;
    }

    public function index()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $outlets = PosOutlet::where('property_id', $property->id)->get();
        if ($outlets->isEmpty()) {
            // Seed a default restaurant outlet if none exists
            $outlet = PosOutlet::create([
                'property_id' => $property->id,
                'name'        => 'Main Restaurant & Bar',
                'code'        => 'REST-01',
                'type'        => 'restaurant',
                'is_active'   => true,
            ]);
            $outlets = collect([$outlet]);
        }

        $activeStays = Reservation::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with(['primaryGuest', 'rooms.roomType'])
            ->get();

        // Ensure default menu items exist
        $menuItems = \Illuminate\Support\Facades\DB::table('pos_menu_items')
            ->where('property_id', $property->id)
            ->get();

        if ($menuItems->isEmpty() && !$outlets->isEmpty()) {
            $defaultOutletId = $outlets->first()->id;
            $items = [
                ['name' => 'Grilled Ribeye Steak (300g)', 'category' => 'mains', 'price_minor' => 3500, 'icon' => 'bi-pie-chart-fill', 'badge' => 'Chef Special'],
                ['name' => 'Swahili Seafood Platter', 'category' => 'mains', 'price_minor' => 4200, 'icon' => 'bi-water', 'badge' => 'Popular'],
                ['name' => 'Nyama Choma Platters', 'category' => 'mains', 'price_minor' => 2800, 'icon' => 'bi-fire', 'badge' => 'Local Favorite'],
                ['name' => 'Tusker Lager (500ml)', 'category' => 'beverages', 'price_minor' => 450, 'icon' => 'bi-cup-straw', 'badge' => 'Chilled'],
                ['name' => 'Fresh Passion Fruit Juice', 'category' => 'beverages', 'price_minor' => 350, 'icon' => 'bi-cup-hot', 'badge' => 'Fresh'],
                ['name' => 'Espresso / Cappuccino', 'category' => 'beverages', 'price_minor' => 300, 'icon' => 'bi-cup', 'badge' => 'Organic'],
                ['name' => 'Zanzibar Spice Cake', 'category' => 'desserts', 'price_minor' => 650, 'icon' => 'bi-egg-fried', 'badge' => 'Sweet'],
                ['name' => 'Tropical Fruit Bowl', 'category' => 'desserts', 'price_minor' => 500, 'icon' => 'bi-basket', 'badge' => 'Healthy'],
                ['name' => 'Deep Tissue Massage (60 min)', 'category' => 'services', 'price_minor' => 6000, 'icon' => 'bi-heart-pulse', 'badge' => 'Spa'],
            ];

            foreach ($items as $item) {
                \Illuminate\Support\Facades\DB::table('pos_menu_items')->insert([
                    'property_id'   => $property->id,
                    'pos_outlet_id' => $defaultOutletId,
                    'name'          => $item['name'],
                    'category'      => $item['category'],
                    'price_minor'   => $item['price_minor'],
                    'is_taxable'    => true,
                    'is_available'  => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $menuItems = \Illuminate\Support\Facades\DB::table('pos_menu_items')
                ->where('property_id', $property->id)
                ->get();
        }

        $recentOrders = PosOrder::where('property_id', $property->id)
            ->with(['outlet', 'reservation.primaryGuest'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.pos.terminal', compact('property', 'outlets', 'activeStays', 'recentOrders', 'menuItems'));
    }

    public function storeOrder(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'pos_outlet_id'  => 'required|exists:pos_outlets,id',
            'order_type'     => 'required|in:dine_in,takeaway,room_charge',
            'table_number'   => 'nullable|string',
            'reservation_id' => 'nullable|required_if:order_type,room_charge|exists:reservations,id',
            'item_name'      => 'required|string',
            'amount'         => 'required|numeric|min:0.01',
        ]);

        $amountMinor = (int) round($data['amount'] * 100);

        $order = PosOrder::create([
            'property_id'    => $property->id,
            'pos_outlet_id'  => $data['pos_outlet_id'],
            'reservation_id' => $data['reservation_id'] ?? null,
            'server_user_id' => auth()->id(),
            'order_type'     => $data['order_type'],
            'table_number'   => $data['table_number'] ?? 'T-01',
            'status'         => 'open',
            'payment_status' => 'unpaid',
            'subtotal_minor' => $amountMinor,
            'tax_minor'      => 0,
            'total_minor'    => $amountMinor,
        ]);

        if ($data['order_type'] === 'room_charge' && $order->reservation_id) {
            $this->postingService->postOrderToRoom($order, auth()->user());
            return redirect()->back()->with('success', "POS order {$order->ulid} posted to guest room folio!");
        }

        return redirect()->back()->with('success', "POS order {$order->ulid} created successfully!");
    }

    public function stkPush(Request $request, \App\Domain\Payments\Services\PaymentGatewayRegistry $registry)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $request->validate([
            'phone'  => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $mpesaAdapter = $registry->get('mpesa');
        $amountMinor  = (int) round($request->amount * 100);

        $result = $mpesaAdapter->stkPush(
            $request->phone,
            $amountMinor,
            'POS-' . Str::upper(Str::random(6)),
            'POS Outlet Payment'
        );

        return response()->json($result);
    }

    public function stripeCharge(Request $request, \App\Domain\Payments\Services\PaymentGatewayRegistry $registry)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $stripeAdapter = $registry->get('stripe');
        $amountMinor   = (int) round($request->amount * 100);

        $result = $stripeAdapter->authorize([
            'amount_minor' => $amountMinor,
            'currency'     => $property->currency ?? 'USD',
            'description'  => 'POS Terminal Sale',
        ]);

        return response()->json($result);
    }
}
