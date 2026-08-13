<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\CorporateAccount;
use App\Infrastructure\Persistence\GroupAllotment;
use App\Infrastructure\Persistence\Property;
use Illuminate\Http\Request;

class GroupAllotmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Corporate Accounts & Group Allotments Roster.
     */
    public function index(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $corporateAccounts = $property ? CorporateAccount::where('property_id', $property->id)->latest()->get() : collect();
        $groupAllotments   = $property ? GroupAllotment::where('property_id', $property->id)->with('corporateAccount')->latest()->get() : collect();

        return view('admin.modules.group_allotments', compact(
            'property', 'corporateAccounts', 'groupAllotments'
        ));
    }

    /**
     * Store new Corporate Account.
     */
    public function storeCorporate(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $request->validate([
            'company_name'       => 'required|string|max:255',
            'code'               => 'required|string|max:50|unique:corporate_accounts,code',
            'credit_limit'       => 'required|numeric|min:0',
            'contact_name'       => 'nullable|string',
            'contact_email'      => 'nullable|email',
        ]);

        try {
            CorporateAccount::create([
                'property_id'        => $property->id,
                'company_name'       => $request->input('company_name'),
                'code'               => strtoupper($request->input('code')),
                'credit_limit_minor' => (int) round($request->input('credit_limit') * 100),
                'contact_name'       => $request->input('contact_name'),
                'contact_email'      => $request->input('contact_email'),
                'status'             => 'active',
            ]);

            return redirect()->back()->with('success', 'Corporate account created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create corporate account: ' . $e->getMessage());
        }
    }

    /**
     * Store new Group Allotment.
     */
    public function storeAllotment(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $request->validate([
            'corporate_account_id' => 'nullable|exists:corporate_accounts,id',
            'name'                 => 'required|string|max:255',
            'code'                 => 'required|string|max:50',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after:start_date',
            'rooms_allocated'      => 'required|integer|min:1',
            'negotiated_rate'      => 'required|numeric|min:0',
        ]);

        try {
            GroupAllotment::create([
                'property_id'           => $property->id,
                'corporate_account_id'  => $request->input('corporate_account_id'),
                'name'                  => $request->input('name'),
                'code'                  => strtoupper($request->input('code')),
                'start_date'            => $request->input('start_date'),
                'end_date'              => $request->input('end_date'),
                'rooms_allocated'       => $request->input('rooms_allocated'),
                'rooms_picked_up'       => 0,
                'negotiated_rate_minor' => (int) round($request->input('negotiated_rate') * 100),
                'status'                => 'active',
            ]);

            return redirect()->back()->with('success', 'Group room block allotment created.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create group allotment: ' . $e->getMessage());
        }
    }
}
