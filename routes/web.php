<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Guest / Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Staff Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Staff Admin Routes (authenticated)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-property', [DashboardController::class, 'switchProperty'])->name('switch.property');

    // Organizations (platform admin)
    Route::resource('organizations', OrganizationController::class)->except(['destroy']);

    // Properties
    Route::resource('properties', PropertyController::class)->except(['destroy']);

    // Room Types
    Route::resource('room-types', \App\Http\Controllers\Admin\RoomTypeController::class)->except(['destroy']);

    // Physical Rooms
    Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class)->except(['destroy']);

    // Rate Plans
    Route::resource('rate-plans', \App\Http\Controllers\Admin\RatePlanController::class)->except(['destroy']);
    Route::post('rate-plans/{ratePlan}/daily-rates', [\App\Http\Controllers\Admin\RatePlanController::class, 'saveDailyRates'])->name('rate-plans.daily-rates');

    // Taxes & Fees
    Route::resource('taxes', \App\Http\Controllers\Admin\TaxController::class)->only(['index', 'store', 'update']);

    // Phase 4 Reservations Core
    Route::resource('reservations', \App\Http\Controllers\Admin\ReservationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('reservations/{reservation}/status', [\App\Http\Controllers\Admin\ReservationController::class, 'updateStatus'])->name('reservations.status');
    Route::post('reservations/{reservation}/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('reservations.payments.store');
    Route::get('reservations/{reservation}/invoice', [\App\Http\Controllers\Admin\PaymentController::class, 'downloadInvoice'])->name('reservations.invoice.download');
    Route::post('reservations/{reservation}/send-confirmation', [\App\Http\Controllers\Admin\PaymentController::class, 'resendConfirmationEmail'])->name('reservations.send-confirmation');
    // Phase 7 Front Desk & Stay Management
    Route::get('tape-chart',                            [\App\Http\Controllers\Admin\FrontDeskController::class, 'tapeChart'])->name('tape-chart.index');
    Route::get('arrivals',                              [\App\Http\Controllers\Admin\FrontDeskController::class, 'arrivals'])->name('front-desk.arrivals');
    Route::get('departures',                            [\App\Http\Controllers\Admin\FrontDeskController::class, 'departures'])->name('front-desk.departures');
    Route::get('in-house',                              [\App\Http\Controllers\Admin\FrontDeskController::class, 'inHouse'])->name('front-desk.in-house');

    Route::post('reservations/{reservation}/check-in',  [\App\Http\Controllers\Admin\FrontDeskController::class, 'checkIn'])->name('front-desk.check-in');
    Route::post('stays/{stay}/check-out',               [\App\Http\Controllers\Admin\FrontDeskController::class, 'checkOut'])->name('front-desk.check-out');
    Route::post('stays/{stay}/move-room',               [\App\Http\Controllers\Admin\FrontDeskController::class, 'moveRoom'])->name('front-desk.move-room');
    Route::post('reservations/{reservation}/no-show',   [\App\Http\Controllers\Admin\FrontDeskController::class, 'markNoShow'])->name('front-desk.no-show');
    // Phase 9 Housekeeping
    Route::get('housekeeping',                                          [\App\Http\Controllers\Admin\HousekeepingController::class, 'index'])->name('housekeeping.index');
    Route::post('housekeeping/rooms/{room}/status',                     [\App\Http\Controllers\Admin\HousekeepingController::class, 'updateRoomStatus'])->name('housekeeping.room-status');
    Route::post('housekeeping/rooms/{room}/sign-off',                   [\App\Http\Controllers\Admin\HousekeepingController::class, 'signOff'])->name('housekeeping.sign-off');
    Route::post('housekeeping/tasks/{task}/assign',                     [\App\Http\Controllers\Admin\HousekeepingController::class, 'assignTask'])->name('housekeeping.task.assign');
    Route::post('housekeeping/tasks/{task}/complete',                   [\App\Http\Controllers\Admin\HousekeepingController::class, 'completeTask'])->name('housekeeping.task.complete');
    Route::post('housekeeping/maintenance',                             [\App\Http\Controllers\Admin\HousekeepingController::class, 'storeMaintenance'])->name('housekeeping.maintenance.store');
    Route::post('housekeeping/maintenance/{maintenance}/update',        [\App\Http\Controllers\Admin\HousekeepingController::class, 'updateMaintenance'])->name('housekeeping.maintenance.update');

    // Phase 8 Folios, Cashiering & Invoices
    Route::get('folios',                                        [\App\Http\Controllers\Admin\FolioController::class, 'index'])->name('folios.index');
    Route::get('folios/{folio}',                               [\App\Http\Controllers\Admin\FolioController::class, 'show'])->name('folios.show');
    Route::post('folios/{folio}/charge',                        [\App\Http\Controllers\Admin\FolioController::class, 'postCharge'])->name('folios.charge');
    Route::post('folios/{folio}/payment',                       [\App\Http\Controllers\Admin\FolioController::class, 'postPayment'])->name('folios.payment');
    Route::post('folio-transactions/{transaction}/reverse',        [\App\Http\Controllers\Admin\FolioController::class, 'reverse'])->name('folios.reverse');
    Route::get('cashier-shifts',                                [\App\Http\Controllers\Admin\FolioController::class, 'shiftsIndex'])->name('folios.shifts.index');
    Route::post('cashier-shifts/open',                          [\App\Http\Controllers\Admin\FolioController::class, 'openShift'])->name('folios.shifts.open');
    Route::post('cashier-shifts/{shift}/close',                 [\App\Http\Controllers\Admin\FolioController::class, 'closeShift'])->name('folios.shifts.close');

    // Phase 9 Night Audit
    Route::get('night-audit',                                   [\App\Http\Controllers\Admin\NightAuditController::class, 'index'])->name('night-audit.index');
    Route::post('night-audit/run',                              [\App\Http\Controllers\Admin\NightAuditController::class, 'run'])->name('night-audit.run');
    Route::post('night-audit/{audit}/reset',                    [\App\Http\Controllers\Admin\NightAuditController::class, 'reset'])->name('night-audit.reset');

    // Phase 10 Reporting & Analytics
    Route::get('reports',                                       [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/csv',                            [\App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('reports/export/pdf',                            [\App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    // Phase 11 Channel Manager & Dead-Letter Queue
    Route::get('channel-manager',                               [\App\Http\Controllers\Admin\ChannelManagerController::class, 'index'])->name('channel-manager.index');
    Route::post('channel-manager/sync',                         [\App\Http\Controllers\Admin\ChannelManagerController::class, 'triggerSync'])->name('channel-manager.sync');
    Route::post('channel-manager/connections/update',           [\App\Http\Controllers\Admin\ChannelManagerController::class, 'updateConnection'])->name('channel-manager.connection.update');

    Route::get('dead-letter-queue',                             [\App\Http\Controllers\Admin\DeadLetterQueueController::class, 'index'])->name('dead-letter.index');
    Route::post('dead-letter-queue/{item}/replay',              [\App\Http\Controllers\Admin\DeadLetterQueueController::class, 'replay'])->name('dead-letter.replay');
    Route::post('dead-letter-queue/{item}/discard',             [\App\Http\Controllers\Admin\DeadLetterQueueController::class, 'discard'])->name('dead-letter.discard');

    // Phase 12 Production Readiness & System Health
    Route::get('system/health',                                 [\App\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('system.health');
    Route::post('system/health/diagnostics',                    [\App\Http\Controllers\Admin\SystemHealthController::class, 'runDiagnostics'])->name('system.health.diagnostics');

    // Phase 13 Scale & Advanced Features
    Route::get('group-allotments',                              [\App\Http\Controllers\Admin\GroupAllotmentController::class, 'index'])->name('group-allotments.index');
    Route::post('corporate-accounts',                           [\App\Http\Controllers\Admin\GroupAllotmentController::class, 'storeCorporate'])->name('corporate-accounts.store');
    Route::post('group-allotments',                             [\App\Http\Controllers\Admin\GroupAllotmentController::class, 'storeAllotment'])->name('group-allotments.store');

    Route::get('loyalty',                                       [\App\Http\Controllers\Admin\LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::post('loyalty/enroll',                               [\App\Http\Controllers\Admin\LoyaltyController::class, 'enroll'])->name('loyalty.enroll');
    Route::post('loyalty/accounts/{account}/adjust',            [\App\Http\Controllers\Admin\LoyaltyController::class, 'adjustPoints'])->name('loyalty.adjust');

    // Phase 3 Inventory & Pricing Core
    Route::get('inventory/matrix',          [\App\Http\Controllers\Admin\InventoryController::class, 'matrix'])->name('inventory.matrix');
    Route::post('inventory/adjust',         [\App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('inventory/holds/{hold}/release', [\App\Http\Controllers\Admin\InventoryController::class, 'releaseHold'])->name('inventory.holds.release');
    Route::get('quotes/inspector',          [\App\Http\Controllers\Admin\QuoteInspectorController::class, 'index'])->name('quotes.index');
    Route::post('quotes/generate',          [\App\Http\Controllers\Admin\QuoteInspectorController::class, 'generate'])->name('quotes.generate');

    // Users
    Route::resource('users', UserController::class)->except(['destroy']);
});

use App\Http\Controllers\Guest\BookingEngineController;
use App\Http\Controllers\Guest\GuestPortalController;

/*
|--------------------------------------------------------------------------
| Booking Engine (Phase 5 — Public Guest Routes)
|--------------------------------------------------------------------------
*/

Route::prefix('booking')->name('booking.')->group(function () {
    // Portal routes
    Route::get('/portal/lookup',                            [GuestPortalController::class, 'lookupForm'])->name('portal.lookup');
    Route::post('/portal/search',                           [GuestPortalController::class, 'searchReservation'])->name('portal.search');
    Route::get('/portal/reservation/{confirmationNumber}', [GuestPortalController::class, 'showReservation'])->name('portal.show');
    Route::post('/portal/reservation/{confirmationNumber}/cancel', [GuestPortalController::class, 'cancelReservation'])->name('portal.cancel');

    // Property Guest Booking Engine Funnel
    Route::get('/{slug}',                                   [BookingEngineController::class, 'index'])->name('index');
    Route::get('/{slug}/widget',                            [BookingEngineController::class, 'widget'])->name('widget');
    Route::get('/{slug}/search',                            [BookingEngineController::class, 'search'])->name('search');
    Route::match(['get', 'post'], '/{slug}/addons',        [BookingEngineController::class, 'addons'])->name('addons');
    Route::match(['get', 'post'], '/{slug}/guest-details', [BookingEngineController::class, 'guestDetails'])->name('guest-details');
    Route::match(['get', 'post'], '/{slug}/review',        [BookingEngineController::class, 'reviewAndHold'])->name('review');
    Route::match(['get', 'post'], '/{slug}/confirm',       [BookingEngineController::class, 'confirm'])->name('confirm');
    Route::get('/{slug}/confirmation/{confirmationNumber}', [BookingEngineController::class, 'confirmation'])->name('confirmation');
});
