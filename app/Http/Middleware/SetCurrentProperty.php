<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentProperty
{
    /**
     * Resolve the active property from session or request and bind it to the container.
     * This allows controllers and services to call app('current.property').
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $propertyId = session('current_property_id');
            $property   = null;

            if ($propertyId) {
                $candidate = \App\Infrastructure\Persistence\Property::find($propertyId);
                if ($candidate && $user->canAccessProperty($candidate)) {
                    $property = $candidate;
                }
            }

            if (!$property) {
                // Find first property the user is explicitly authorized to access
                if ($user->is_platform_admin) {
                    $property = \App\Infrastructure\Persistence\Property::first();
                } else {
                    $property = $user->assignedProperties()
                        ->where('properties.organization_id', $user->organization_id)
                        ->where('property_user_assignments.is_active', true)
                        ->where(fn($q) => $q->whereNull('property_user_assignments.expires_at')
                                            ->orWhere('property_user_assignments.expires_at', '>', now()))
                        ->first();
                }

                if ($property) {
                    session(['current_property_id' => $property->id]);
                } else {
                    session()->forget('current_property_id');
                }
            }

            if ($property && $user->canAccessProperty($property)) {
                app()->instance('current.property', $property);
                $request->attributes->set('current_property', $property);
            }

            app()->instance('current.user', $user);
        }

        return $next($request);
    }
}
