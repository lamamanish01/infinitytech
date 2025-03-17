<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BandwidthController;
use App\Http\Controllers\PermissionController;
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
    Route::resource('/bandwidth', BandwidthController::class);
    Route::resource('/internetplan', InternetPlanController::class);
});
