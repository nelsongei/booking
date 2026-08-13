<?php

namespace App\Http\Controllers\Api;

use App\Domain\Integrations\ChannelManagerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookReceiverController extends Controller
{
    protected ChannelManagerService $channelManagerService;

    public function __construct(ChannelManagerService $channelManagerService)
    {
        $this->channelManagerService = $channelManagerService;
    }

    /**
     * Handle incoming OTA webhooks (Booking.com, Airbnb, Expedia, Stripe, etc.)
     */
    public function handleWebhook(Request $request, string $provider)
    {
        $payload = $request->all();

        if (empty($payload)) {
            return response()->json(['error' => 'Empty payload received'], 400);
        }

        try {
            $webhookEvent = $this->channelManagerService->processInboundWebhook(
                $provider,
                $payload,
                $request->header('X-Event-ID') ?: ($payload['event_id'] ?? null)
            );

            return response()->json([
                'success'    => true,
                'status'     => $webhookEvent->status,
                'event_ulid' => $webhookEvent->ulid,
                'message'    => 'Webhook received and processed successfully',
            ], 200);
        } catch (\Exception $e) {
            // Even when processing fails internally and logs to dead letter, return 202 accepted to prevent infinite endpoint retry hammering from provider
            return response()->json([
                'success' => false,
                'status'  => 'failed_queued_to_dead_letter',
                'message' => 'Webhook received but failed processing: ' . $e->getMessage(),
            ], 202);
        }
    }
}
