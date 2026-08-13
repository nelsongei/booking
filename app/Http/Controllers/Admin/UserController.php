<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\PropertyUserAssignment;
use App\Infrastructure\Persistence\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user  = auth()->user();
        $query = User::with(['organization', 'roles']);

        if (!$user->is_platform_admin) {
            $query->where('organization_id', $user->organization_id);
        }

        $users = $query->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $user          = auth()->user();
        $roles         = Role::orderBy('name')->get();
        $organizations = $user->is_platform_admin
            ? Organization::orderBy('name')->get()
            : Organization::where('id', $user->organization_id)->get();
        $properties    = $user->is_platform_admin
            ? Property::orderBy('name')->get()
            : Property::where('organization_id', $user->organization_id)->orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'organizations', 'properties'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8|confirmed',
            'organization_id' => 'required|exists:organizations,id',
            'roles'           => 'nullable|array',
            'roles.*'         => 'exists:roles,name',
            'property_ids'    => 'nullable|array',
            'property_ids.*'  => 'exists:properties,id',
            'status'          => 'nullable|in:active,suspended,invited',
        ]);

        if (!$currentUser->is_platform_admin) {
            abort_unless($data['organization_id'] == $currentUser->organization_id, 403);
        }

        $user = User::create([
            'ulid'            => (string) Str::ulid(),
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'organization_id' => $data['organization_id'],
            'status'          => $data['status'] ?? 'active',
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        // Assign to properties
        if (!empty($data['property_ids'])) {
            foreach ($data['property_ids'] as $propertyId) {
                $property = Property::find($propertyId);
                if ($property && $property->organization_id == $data['organization_id']) {
                    $roleName = $data['roles'][0] ?? 'front-desk-agent';
                    PropertyUserAssignment::firstOrCreate(
                        ['user_id' => $user->id, 'property_id' => $propertyId, 'role_name' => $roleName],
                        ['organization_id' => $data['organization_id'], 'is_active' => true]
                    );
                }
            }
        }

        AuditService::log('user.created', 'User', $user->ulid, null, ['name' => $user->name, 'email' => $user->email]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User '{$user->name}' created successfully.");
    }

    public function show(User $user)
    {
        $this->authorizeUserAccess($user);
        $user->load(['organization', 'roles', 'propertyAssignments.property']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorizeUserAccess($user);
        $roles      = Role::orderBy('name')->get();
        $properties = Property::where('organization_id', $user->organization_id)->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles', 'properties'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserAccess($user);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'status'       => 'required|in:active,suspended,invited',
            'roles'        => 'nullable|array',
            'roles.*'      => 'exists:roles,name',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $before = $user->only(['name', 'email', 'status']);

        $user->update([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'status' => $data['status'],
        ]);

        if (!empty($data['new_password'])) {
            $user->update(['password' => Hash::make($data['new_password'])]);
        }

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        AuditService::log('user.updated', 'User', $user->ulid, $before, $user->fresh()->only(['name', 'email', 'status']));

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.');
    }

    private function authorizeUserAccess(User $user): void
    {
        $current = auth()->user();
        abort_unless($current->is_platform_admin || $current->organization_id === $user->organization_id, 403, 'Access denied.');
    }
}
