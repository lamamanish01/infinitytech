<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InternetPlanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NasController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Tr069DeviceController;
use App\Http\Controllers\Tr069ServerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::view('about', 'about')->name('about');

    Route::resource('/users', UserController::class);

    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])
        ->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])
        ->name('profile.update');

    Route::resource('/roles', RoleController::class);
    Route::resource('/permissions', PermissionController::class);

    Route::resource('/branch', BranchController::class);
    Route::resource('/internetplan', InternetPlanController::class);
    Route::resource('/menus', MenuController::class);
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


    Route::get('/recharges/create/{customerId}', [RechargeController::class, 'create'])
        ->name('recharges.create');
    Route::post('/recharges', [RechargeController::class, 'store'])
        ->name('recharges.store');
    Route::get('/recharges/edit/{customerId}', [RechargeController::class, 'edit'])
        ->name('recharges.edit');
    Route::patch('/recharges', [RechargeController::class, 'update'])
        ->name('recharges.update');
    // Route::post('/provide-grace/{customerId}', [RechargeController::class, 'provideGrace'])
    //     ->name('provide-grace');

    Route::resource('/tr069server', Tr069ServerController::class);
    Route::resource('/tr069device', Tr069DeviceController::class);

    Route::resource('/nas', NasController::class);

    Route::resource('/ticket', TicketController::class);
    Route::post('/ticket/reply/{id}',[TicketController::class, 'reply'])->name('ticket.reply');
    Route::post('/ticket/close/{id}',[TicketController::class, 'close'])->name('ticket.close');
});
