<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ObservabilityServiceProvider extends ServiceProvider
{
    /**
     * Register observability services.
     */
    public function register(): void
    {
        $this->app->singleton('observability.redactor', function () {
            return new class {
                protected array $sensitiveKeys = [
                    'password', 'password_confirmation', 'secret', 'token',
                    'auth_token', 'credit_card', 'card_number', 'cvv',
                    'cvc', 'mfa_secret', 'stk_push_passkey', 'api_key',
                ];

                public function redact(array $data): array
                {
                    foreach ($data as $key => $value) {
                        if (is_array($value)) {
                            $data[$key] = $this->redact($value);
                        } elseif (in_array(strtolower($key), $this->sensitiveKeys, true)) {
                            $data[$key] = '[REDACTED]';
                        }
                    }
                    return $data;
                }
            };
        });
    }

    /**
     * Bootstrap observability telemetry.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        // Enrich logger context with telemetry headers if available
        $correlationId = request()->header('X-Correlation-ID', (string) Str::uuid());

        $context = [
            'correlation_id' => $correlationId,
        ];

        if (auth()->check()) {
            $user = auth()->user();
            $context['user_id']         = $user->id;
            $context['organization_id'] = $user->organization_id;
        }

        if (app()->bound('current.property')) {
            $property = app('current.property');
            if ($property) {
                $context['property_id'] = $property->id;
            }
        }

        Log::shareContext($context);
    }
}
