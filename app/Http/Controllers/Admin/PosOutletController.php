<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pos\Models\PosOutlet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PosOutletController extends Controller
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

        return view('admin.pos.outlets', compact('property', 'outlets'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|in:restaurant,bar,spa,shop,minibar',
        ]);

        PosOutlet::create([
            'property_id' => $property->id,
            'name'        => $data['name'],
            'code'        => $data['code'] ?? strtoupper(substr($data['name'], 0, 4)),
            'type'        => $data['type'],
            'is_active'   => true,
        ]);

        return redirect()->back()->with('success', "Outlet '{$data['name']}' created successfully!");
    }
}
