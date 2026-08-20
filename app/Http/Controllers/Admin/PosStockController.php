<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pos\Services\StockControlService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosStockController extends Controller
{
    protected StockControlService $stockService;

    public function __construct(StockControlService $stockService)
    {
        $this->middleware('auth');
        $this->stockService = $stockService;
    }

    public function index()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $stockItems = DB::table('stock_items')->where('property_id', $property->id)->get();
        $lowStock   = $this->stockService->getLowStockAlerts($property->id);

        return view('admin.pos.stock', compact('property', 'stockItems', 'lowStock'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'unit_of_measure'  => 'required|string|max:50',
            'quantity_on_hand' => 'required|numeric|min:0',
            'reorder_level'    => 'required|numeric|min:0',
            'unit_cost'        => 'required|numeric|min:0',
        ]);

        DB::table('stock_items')->insert([
            'property_id'      => $property->id,
            'name'             => $data['name'],
            'unit_of_measure'  => $data['unit_of_measure'],
            'quantity_on_hand' => $data['quantity_on_hand'],
            'reorder_level'    => $data['reorder_level'],
            'unit_cost_minor'  => (int) round($data['unit_cost'] * 100),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->back()->with('success', "Stock item '{$data['name']}' added to inventory!");
    }
}
