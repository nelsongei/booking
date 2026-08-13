<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeWebhookJob;
use Illuminate\Http\Request;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret', env('STRIPE_WEBHOOK_SECRET'));

        $eventData = null;

        if (!empty($secret) && !empty($sigHeader)) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
                $eventData = [
                    'id'   => $event->id,
                    'type' => $event->type,
                    'data' => $event->data->object->toArray(),
                ];
            } catch (\Exception $e) {
                return response()->json(['error' => 'Webhook Signature Verification Failed: ' . $e->getMessage()], 400);
            }
        } else {
            // Unverified development payload processing
            $json = json_decode($payload, true);
            $eventData = [
                'id'   => $json['id'] ?? ('evt_mock_' . bin2hex(random_bytes(6))),
                'type' => $json['type'] ?? 'payment_intent.succeeded',
                'data' => $json['data']['object'] ?? $json,
            ];
        }

        // Dispatch background processing job
        ProcessStripeWebhookJob::dispatch($eventData);

        return response()->json(['status' => 'Webhook received and job queued successfully.']);
    }
}
