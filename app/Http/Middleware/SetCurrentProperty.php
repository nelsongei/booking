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

            if ($propertyId) {
                $property = \App\Infrastructure\Persistence\Property::find($propertyId);

                if ($property && $user->canAccessProperty($property)) {
                    app()->instance('current.property', $property);
                    $request->attributes->set('current_property', $property);
                }
            }

            app()->instance('current.user', $user);
        }

        return $next($request);
    }
}
