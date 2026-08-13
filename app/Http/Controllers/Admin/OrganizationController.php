<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only platform admins can manage organizations globally
    }

    public function index()
    {
        $this->authorizePlatformAdmin();
        $organizations = Organization::withCount('properties')
            ->orderBy('name')
            ->paginate(20);
        return view('admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        $this->authorizePlatformAdmin();
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        $this->authorizePlatformAdmin();

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:100|unique:organizations',
            'legal_name'       => 'nullable|string|max:255',
            'default_currency' => 'required|string|size:3',
            'default_timezone' => 'required|string|max:100',
            'default_locale'   => 'required|string|max:10',
            'country'          => 'nullable|string|size:2',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:50',
            'website'          => 'nullable|url|max:255',
        ]);

        $data['ulid'] = (string) Str::ulid();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $organization = Organization::create($data);

        AuditService::log('organization.created', 'Organization', $organization->ulid, null, $organization->toArray());

        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', "Organization '{$organization->name}' created successfully.");
    }

    public function show(Organization $organization)
    {
        $this->authorizePlatformAdmin();
        $properties = $organization->properties()->withCount('rooms')->get();
        $users      = $organization->users()->with('roles')->paginate(10);
        return view('admin.organizations.show', compact('organization', 'properties', 'users'));
    }

    public function edit(Organization $organization)
    {
        $this->authorizePlatformAdmin();
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorizePlatformAdmin();

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'legal_name'       => 'nullable|string|max:255',
            'default_currency' => 'required|string|size:3',
            'default_timezone' => 'required|string|max:100',
            'default_locale'   => 'required|string|max:10',
            'country'          => 'nullable|string|size:2',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:50',
            'website'          => 'nullable|url|max:255',
            'status'           => 'required|in:active,suspended,trial',
        ]);

        $before = $organization->toArray();
        $organization->update($data);

        AuditService::log('organization.updated', 'Organization', $organization->ulid, $before, $organization->fresh()->toArray());

        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Organization updated successfully.');
    }

    private function authorizePlatformAdmin(): void
    {
        abort_unless(auth()->user()->is_platform_admin, 403, 'Only platform administrators can manage organizations.');
    }
}
