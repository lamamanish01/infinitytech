<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CronJobController;
use App\Http\Controllers\CronLogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerImportController;
use App\Http\Controllers\CustomSmsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceLogController;
use App\Http\Controllers\InternetPlanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\NasController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RadiusController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServerStatsController;
use App\Http\Controllers\SmsGatewayController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Tr069DeviceController;
use App\Http\Controllers\Tr069ServerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::redirect('/home', '/dashboard')->name('home');

    Route::resource('/users', UserController::class);

    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])
        ->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])
        ->name('profile.update');

    Route::resource('/roles', RoleController::class);
    Route::resource('/dashboard', DashboardController::class);
    Route::resource('/permissions', PermissionController::class);


    Route::post('/branch/add-balance', [BranchController::class, 'addBalance'])
        ->name('branch.addBalance');
    Route::get('/branch/transactions', [BranchController::class, 'transactions'])
        ->name('branch.transactions');
    Route::delete('/branch-transaction/{id}', [BranchController::class, 'destroy'])
        ->name('branchTransaction.delete');
    Route::resource('/branch', BranchController::class);

    Route::resource('/internetplan', InternetPlanController::class);
    Route::resource('/menus', MenuController::class);

    Route::get('/customers/online', [CustomerController::class, 'online']);
    Route::get('/customers/expired', [CustomerController::class, 'expired']);
    Route::get('/customers/expiring', [CustomerController::class, 'expiring'])
        ->name('customers.expiring');
    Route::get('/customers/import', [CustomerImportController::class, 'showForm'])->name('customers.import.form');
    Route::post('/customers/import', [CustomerImportController::class, 'import'])->name('customers.import');
    Route::get('/customers/import/template', [CustomerImportController::class, 'downloadTemplate'])->name('customers.import.template');
    Route::resource('/customers', CustomerController::class);
    Route::get('/customers/{customer}/expiry', [CustomerController::class, 'expiryForm'])
        ->name('customers.expiry-form');
    Route::post('/customers/{customer}/change-expiry', [CustomerController::class, 'changeExpiry'])
        ->name('customers.change-expiry');
    Route::post('/provide-grace/{customerId}', [CustomerController::class, 'provideGrace'])
        ->name('provide-grace');
    Route::post('/customer/{id}/disconnect', [CustomerController::class, 'disconnect'])
        ->name('customer.disconnect');
    Route::post('/customer/{id}/force-disconnect', [CustomerController::class, 'forceDisconnect'])
        ->name('customer.forceDisconnect');
    Route::post('/customers/{id}/mac/bind', [CustomerController::class, 'bindMac'])->name('customer.bind-mac');
    Route::post('/customers/{id}/mac/unbind', [CustomerController::class, 'unbindMac'])->name('customer.unbind-mac');
    Route::get('/customers/online', [CustomerController::class, 'searchOnline'])->name('customers.online');

    Route::prefix('tr069')->middleware('auth')->group(function () {
        Route::post(
            '/device/{id}/router-update',
            [Tr069DeviceController::class, 'routerMgmtUpdate']
        )->name('tr069.device.router.update');

        Route::post(
            '/device/{id}/reboot',
            [Tr069DeviceController::class, 'reboot']
        )->name('tr069.device.reboot');

        Route::post(
            '/device/{id}/factory-reset',
            [Tr069DeviceController::class, 'factoryReset']
        )->name('tr069.device.factory-reset');

        Route::post(
            '/device/{id}/push-acs',
            [Tr069DeviceController::class, 'pushAcs']
        )->name('tr069.device.push-acs');

        Route::get(
            '/device/{id}/logs',
            [Tr069DeviceController::class, 'logs']
        )->name('tr069.device.logs');
    });

    Route::get('/recharges/create/{customerId}', [RechargeController::class, 'create'])
        ->name('recharges.create');
    Route::post('/recharges', [RechargeController::class, 'store'])
        ->name('recharges.store');
    Route::get('/recharges/edit/{customerId}', [RechargeController::class, 'edit'])
        ->name('recharges.edit');
    Route::patch('/recharges', [RechargeController::class, 'update'])
        ->name('recharges.update');

    Route::resource('/billing', BillingController::class);

    Route::resource('/tr069server', Tr069ServerController::class);
    Route::resource('/tr069device', Tr069DeviceController::class);

    Route::resource('/nas', NasController::class);

    Route::resource('/ticket', TicketController::class);
    Route::post('/ticket/reply/{id}',[TicketController::class, 'reply'])->name('ticket.reply');
    Route::post('/ticket/close/{id}',[TicketController::class, 'close'])->name('ticket.close');
    Route::post('/{id}/assign', [TicketController::class, 'assign'])
        ->name('tickets.assign');

    Route::post('/{id}/reply', [TicketController::class, 'reply'])
        ->name('tickets.reply');

    Route::post('/{id}/customer-reply', [TicketController::class, 'customerReply'])
        ->name('tickets.customer-reply');

    Route::post('/{id}/internal-note', [TicketController::class, 'internalNote'])
        ->name('tickets.internal-note');

    Route::post('/{id}/status', [TicketController::class, 'updateStatus'])
        ->name('tickets.status');

    Route::prefix('sms')->name('sms.')->group(function () {

        // SMS Gateway
        Route::get('/', [SmsGatewayController::class, 'index'])->name('index');
        Route::get('/create', [SmsGatewayController::class, 'create'])->name('create');
        Route::post('/store', [SmsGatewayController::class, 'store'])->name('store');

        // Edit & Update
        Route::get('/{id}/edit', [SmsGatewayController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SmsGatewayController::class, 'update'])->name('update');

        // Delete Gateway
        Route::delete('/{id}', [SmsGatewayController::class, 'destroy'])->name('destroy');

        // Custom SMS
        Route::get('/custom', [CustomSmsController::class, 'create'])->name('custom.create');
        Route::post('/custom', [CustomSmsController::class, 'store'])->name('custom.store');

        // SMS Queue
        Route::get('/queues', [SmsGatewayController::class, 'queues'])->name('queues');
        Route::delete('/queues/{id}', [SmsGatewayController::class, 'queueDelete'])->name('queues.delete');

        // SMS Logs
        Route::get('/logs', [SmsGatewayController::class, 'logs'])->name('logs');

        // Send SMS
        Route::post('/send', [SmsGatewayController::class, 'send'])->name('send');
    });

    Route::resource('mikrotik', MikrotikController::class);

    Route::get('/cron', [CronLogController::class, 'index'])->name('cron');
    Route::delete('/cron-clear-all', [CronLogController::class, 'clearAll'])
        ->name('cron.clearAll');

    Route::get('/cron-jobs', [CronJobController::class, 'index']);
    Route::post('/cron-jobs/store', [CronJobController::class, 'store']);
    Route::post('/cron-jobs/{id}/toggle', [CronJobController::class, 'toggle']);
    Route::post('/cron-jobs/{id}/frequency', [CronJobController::class, 'updateFrequency']);
    Route::delete('/cron-jobs/{id}', [CronJobController::class, 'destroy']);

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/activities', [ActivityLogController::class, 'index'])
        ->name('activities.index');
    Route::get('/activity/{id}/read', [ActivityLogController::class, 'read'])
        ->name('activity.read');
    Route::get('/activities/mark-all-read', [ActivityLogController::class, 'markAllRead'])
        ->name('activities.markAllRead');

    Route::resource('radius', RadiusController::class);

    Route::get('/devices', [DeviceLogController::class, 'index'])->name('devices.index');
    Route::get('/devices/{id}', [DeviceLogController::class, 'show'])->name('devices.show');
    Route::post('/devices/revoke', [DeviceLogController::class, 'revoke'])->name('devices.revoke');
    Route::delete('/devices/clear', [DeviceLogController::class, 'clearLogs'])->name('devices.clear');

    Route::get('/admin/stats', [ServerStatsController::class, 'index'])->name('stats.index');
    Route::get('/api/stats', [ServerStatsController::class, 'json'])->name('stats.json');
});
