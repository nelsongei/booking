<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\WebhookReceiverController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook'])->name('api.webhooks.stripe');
Route::post('/v1/webhooks/{provider}', [WebhookReceiverController::class, 'handleWebhook'])->name('api.webhooks.provider');

// Self-Service Kiosk API & Certified Mobile Key Lock Integration Endpoints
Route::post('/v1/kiosk/checkin', [\App\Http\Controllers\Api\V1\KioskApiController::class, 'kioskCheckin'])->name('api.v1.kiosk.checkin');
Route::post('/v1/mobile-key/issue', [\App\Http\Controllers\Api\V1\KioskApiController::class, 'issueMobileKey'])->name('api.v1.mobile-key.issue');
