<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DigitalRegistrationAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $registrations = DB::table('digital_registration_cards')
            ->where('digital_registration_cards.property_id', $property->id)
            ->join('reservations', 'digital_registration_cards.reservation_id', '=', 'reservations.id')
            ->join('guest_profiles', 'reservations.primary_guest_id', '=', 'guest_profiles.id')
            ->select([
                'digital_registration_cards.*',
                'reservations.confirmation_number',
                'reservations.ulid as reservation_ulid',
                'reservations.check_in_date',
                'reservations.status as reservation_status',
                'guest_profiles.first_name',
                'guest_profiles.last_name',
                'guest_profiles.email',
            ])
            ->orderBy('digital_registration_cards.created_at', 'desc')
            ->paginate(15);

        return view('admin.guest-journey.index', compact('property', 'registrations'));
    }
}
