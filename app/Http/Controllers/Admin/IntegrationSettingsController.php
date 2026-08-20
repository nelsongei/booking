<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class IntegrationSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $config = [
            'mpesa_env'             => env('MPESA_ENV', 'sandbox'),
            'mpesa_consumer_key'    => env('MPESA_CONSUMER_KEY', ''),
            'mpesa_consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
            'mpesa_shortcode'       => env('MPESA_SHORTCODE', '174379'),
            'mpesa_passkey'         => env('MPESA_PASSKEY', ''),
            
            'stripe_key'            => env('STRIPE_KEY', ''),
            'stripe_secret'         => env('STRIPE_SECRET', ''),
            'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        ];

        return view('admin.integrations.settings', compact('property', 'config'));
    }

    public function update(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'mpesa_env'             => 'required|in:sandbox,production',
            'mpesa_consumer_key'    => 'nullable|string',
            'mpesa_consumer_secret' => 'nullable|string',
            'mpesa_shortcode'       => 'nullable|string',
            'mpesa_passkey'         => 'nullable|string',
            
            'stripe_key'            => 'nullable|string',
            'stripe_secret'         => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',
        ]);

        $envFile = base_path('.env');
        if (File::exists($envFile)) {
            $envContent = File::get($envFile);

            $replacements = [
                'MPESA_ENV'             => $data['mpesa_env'],
                'MPESA_CONSUMER_KEY'    => $data['mpesa_consumer_key'] ?? '',
                'MPESA_CONSUMER_SECRET' => $data['mpesa_consumer_secret'] ?? '',
                'MPESA_SHORTCODE'       => $data['mpesa_shortcode'] ?? '',
                'MPESA_PASSKEY'         => $data['mpesa_passkey'] ?? '',
                'STRIPE_KEY'            => $data['stripe_key'] ?? '',
                'STRIPE_SECRET'         => $data['stripe_secret'] ?? '',
                'STRIPE_WEBHOOK_SECRET' => $data['stripe_webhook_secret'] ?? '',
            ];

            foreach ($replacements as $key => $val) {
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}=\"{$val}\"", $envContent);
                } else {
                    $envContent .= "\n{$key}=\"{$val}\"";
                }
            }

            File::put($envFile, $envContent);
        }

        return redirect()->back()->with('success', 'Payment Gateway Integration API Keys updated successfully!');
    }
}
