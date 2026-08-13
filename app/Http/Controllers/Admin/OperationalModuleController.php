<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OperationalModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function reservations()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.reservations', compact('property'));
    }

    public function tapeChart()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.tape_chart', compact('property'));
    }

    public function arrivals()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.arrivals', compact('property'));
    }

    public function departures()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.departures', compact('property'));
    }

    public function inHouse()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.in_house', compact('property'));
    }

    public function housekeeping()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        $rooms = $property ? \App\Infrastructure\Persistence\Room::where('property_id', $property->id)->with('roomType')->get() : collect();
        return view('admin.modules.housekeeping', compact('property', 'rooms'));
    }

    public function folios()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.folios', compact('property'));
    }

    public function nightAudit()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.night_audit', compact('property'));
    }

    public function reports()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        return view('admin.modules.reports', compact('property'));
    }
}
