<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pos\Models\PosOutlet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosMenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $outlets = PosOutlet::where('property_id', $property->id)->get();
        $items   = DB::table('pos_menu_items')->where('property_id', $property->id)->get();

        return view('admin.pos.menu', compact('property', 'outlets', 'items'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'pos_outlet_id' => 'required|exists:pos_outlets,id',
            'name'          => 'required|string|max:255',
            'category'      => 'required|in:mains,beverages,desserts,services,starters',
            'price'         => 'required|numeric|min:0.01',
        ]);

        DB::table('pos_menu_items')->insert([
            'property_id'   => $property->id,
            'pos_outlet_id' => $data['pos_outlet_id'],
            'name'          => $data['name'],
            'category'      => $data['category'],
            'price_minor'   => (int) round($data['price'] * 100),
            'is_taxable'    => true,
            'is_available'  => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->back()->with('success', "Menu item '{$data['name']}' added to catalog!");
    }
}
