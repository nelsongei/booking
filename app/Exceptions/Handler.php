<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->is('booking/*')) {
                $segments = $request->segments();
                $slug = $segments[1] ?? 'tembo-hotel';
                return redirect()->route('booking.search', ['slug' => $slug])
                    ->with('error', 'Your 15-minute room hold or booking session has expired. Please select your dates and room to continue.');
            }
        });

        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->is('booking/*')) {
                $segments = $request->segments();
                $slug = $segments[1] ?? 'tembo-hotel';
                return redirect()->route('booking.search', ['slug' => $slug])
                    ->with('error', 'Your security token or room hold expired due to 15 minutes of inactivity. Please restart your search.');
            }
            return redirect()->back()->with('error', 'Session timed out due to inactivity. Please submit the form again.');
        });

        $this->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Requested resource not found.'], 404);
            }
            return response()->view('errors.404', [], 404);
        });
    }
}
