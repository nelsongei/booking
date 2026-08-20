<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Http\Request;

class GuestInboxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $conversations = Reservation::where('property_id', $property->id)
            ->with(['primaryGuest'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('admin.inbox.index', compact('property', 'conversations'));
    }
}
