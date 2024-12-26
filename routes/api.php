<?php

use App\Http\Controllers\SuperAdminController;

use Illuminate\Support\Facades\Route;

Route::controller(SuperAdminController::class)->group(function () {
    Route::post('/admin/register', 'register');
    Route::post('/admin/login', 'login');

});

// Route::controller(MerchantController::class)->group(function () {
//     Route::post('/merchant/register', 'register');
//     Route::post('/merchant/login', 'login');

// });

Route::middleware(['auth:superadmin'])->group(function () {
    Route::get('/admin/me ', [SuperAdminController::class, 'admin']);
    Route::post('/admin/logout', [SuperAdminController::class, 'logout']);
});

// Route::middleware(['auth:merchant'])->group(function () {
//     Route::get('/merchant/me ', [MerchantController::class, 'merchant']);
//     Route::post('/merchant/logout', [MerchantController::class, 'logout']);
// });

