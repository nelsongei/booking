<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Pricing\QuoteService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\RateQuote;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Http\Request;

class QuoteInspectorController extends Controller
{
    protected QuoteService $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->middleware('auth');
        $this->quoteService = $quoteService;
    }

    /**
     * Display the Quote Calculator & Inspector.
     */
    public function index(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;

        if (!$property) {
            return redirect()->route('admin.properties.index')
                ->with('error', 'Please select or create a property first.');
        }

        $roomTypes = RoomType::where('property_id', $property->id)->where('status', 'active')->get();
        $ratePlans = RatePlan::where('property_id', $property->id)->where('is_active', true)->get();
        $quotes    = RateQuote::where('property_id', $property->id)->latest()->take(10)->get();

        $activeQuote = null;
        if ($request->has('quote_id')) {
            $activeQuote = RateQuote::find($request->quote_id);
        }

        return view('admin.quotes.inspector', compact('property', 'roomTypes', 'ratePlans', 'quotes', 'activeQuote'));
    }

    /**
     * Calculate and generate a new quote.
     */
    public function generate(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'rate_plan_id' => 'required|exists:rate_plans,id',
            'check_in'     => 'required|date',
            'check_out'    => 'required|date|after:check_in',
            'adults'       => 'required|integer|min:1',
            'children'     => 'required|integer|min:0',
            'promo_code'   => 'nullable|string',
        ]);

        $roomType = RoomType::findOrFail($data['room_type_id']);
        $ratePlan = RatePlan::findOrFail($data['rate_plan_id']);

        $quote = $this->quoteService->generateQuote(
            $property, $roomType, $ratePlan,
            $data['check_in'], $data['check_out'],
            $data['adults'], $data['children'],
            $data['promo_code'] ?? null
        );

        return redirect()->route('admin.quotes.index', ['quote_id' => $quote->id])
            ->with('success', 'Rate Quote calculated successfully.');
    }
}
