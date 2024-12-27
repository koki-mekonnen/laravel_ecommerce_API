<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\MerchantController;


use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureTokenIsValid;


Route::controller(SuperAdminController::class)->group(function () {
Route::post('/admin/register', 'register')->name('admin.register');
Route::post('/admin/login', 'login')->name('admin.login');


});
Route::post('/merchant/register', [MerchantController::class, 'register'])->name('merchant.register');
Route::post('/merchant/login', [MerchantController::class, 'login'])->name('merchant.login');




Route::controller(MerchantController::class)->group(function () {

    // Route::post('/merchant/login', 'login');
    Route::get('/merchant/all ', 'index');

});

Route::middleware(['auth:superadmin'])->group(function () {
    Route::get('/admin/me ', [SuperAdminController::class, 'admin']);
    Route::post('/admin/logout', [SuperAdminController::class, 'logout']);
});

Route::middleware(['auth:merchant'])->group(function () {
    Route::get('/merchant/me ', [MerchantController::class, 'merchant']);
    Route::post('/merchant/logout', [MerchantController::class, 'logout']);
});


