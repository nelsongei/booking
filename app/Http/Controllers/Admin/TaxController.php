<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;

        if (!$property) {
            return redirect()->route('admin.properties.index')
                ->with('error', 'Please select or create a property first.');
        }

        $taxes = Tax::where('property_id', $property->id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.taxes.index', compact('property', 'taxes'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'code'                 => 'required|string|max:20',
            'type'                 => 'required|in:percentage,fixed_per_night,fixed_per_stay,fixed_per_person',
            'rate'                 => 'required|numeric|min:0',
            'is_included_in_rate' => 'boolean',
            'applies_to_extras'    => 'boolean',
            'is_active'            => 'boolean',
        ]);

        $data['organization_id']    = $property->organization_id;
        $data['property_id']        = $property->id;
        $data['code']               = strtoupper($data['code']);
        $data['currency']           = $property->currency;
        $data['is_included_in_rate'] = $request->boolean('is_included_in_rate');
        $data['applies_to_extras']    = $request->boolean('applies_to_extras');
        $data['is_active']          = $request->boolean('is_active', true);

        $tax = Tax::create($data);

        AuditService::log('tax.created', 'Tax', (string) $tax->id, null, $tax->toArray(), [
            'property_id' => $property->id,
        ]);

        return redirect()->route('admin.taxes.index')
            ->with('success', "Tax '{$tax->name}' created successfully.");
    }

    public function update(Request $request, Tax $tax)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $tax->property_id === $property->id, 403);

        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'type'                 => 'required|in:percentage,fixed_per_night,fixed_per_stay,fixed_per_person',
            'rate'                 => 'required|numeric|min:0',
            'is_included_in_rate' => 'boolean',
            'applies_to_extras'    => 'boolean',
            'is_active'            => 'boolean',
        ]);

        $before = $tax->toArray();

        $data['is_included_in_rate'] = $request->boolean('is_included_in_rate');
        $data['applies_to_extras']    = $request->boolean('applies_to_extras');
        $data['is_active']          = $request->boolean('is_active');

        $tax->update($data);

        AuditService::log('tax.updated', 'Tax', (string) $tax->id, $before, $tax->fresh()->toArray(), [
            'property_id' => $property->id,
        ]);

        return redirect()->route('admin.taxes.index')
            ->with('success', "Tax '{$tax->name}' updated successfully.");
    }
}
