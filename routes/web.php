<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NasController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\BandwidthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Tr069DeviceController;
use App\Http\Controllers\Tr069ServerController;
use App\Http\Controllers\InternetPlanController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::view('about', 'about')->name('about');

    Route::resource('/users', UserController::class);

    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::resource('/roles', RoleController::class);
    Route::resource('/permissions', PermissionController::class);

    Route::resource('/branch', BranchController::class);
    Route::resource('/internetplan', InternetPlanController::class);
    Route::resource('/menus', MenuController::class);
    Route::resource('/customers', CustomerController::class);
    Route::get('/recharges/create/{customerId}', [RechargeController::class, 'create'])->name('recharges.create');
    Route::post('/recharges', [RechargeController::class, 'store'])->name('recharges.store');
    Route::post('/provide-grace/{customerId}', [RechargeController::class, 'provideGrace'])->name('provide-grace');

    Route::resource('/tr069server', Tr069ServerController::class);
    Route::resource('/tr069device', Tr069DeviceController::class);

    Route::resource('/nas', NasController::class);
});
