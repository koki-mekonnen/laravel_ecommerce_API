<?php

use App\Http\Controllers\MerchantController;

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CategoryController;


use Illuminate\Support\Facades\Route;


// Register and Login Routes (No token required here)

Route::controller(SuperAdminController::class)->group(function () {
Route::post('/admin/register', 'register')->name('admin.register');

Route::post('/admin/login', 'login')->name('admin.login'); // No token check here

});

Route::controller(MerchantController::class)->group(function () {
Route::post('/merchant/register', 'register')->name('merchant.register');
Route::post('/merchant/login', 'login')->name('merchant.login'); // No token check here
Route::get('/merchant/all', 'index');

});

// Protected Routes (Require token validation)
Route::middleware(['ensure.token.valid'])->group(function () {
    // SuperAdmin Routes
    Route::get('/admin/me', [SuperAdminController::class, 'admin'])->name('admin.admin');
    Route::put('/admin/update', [SuperAdminController::class, 'update'])->name('admin.update');
    Route::post('/admin/logout', [SuperAdminController::class, 'logout']);
Route::delete('/admin/merchant/{merchantId}', [SuperAdminController::class, 'deleteMerchant'])->name('admin.merchantdelete');


// Merchant Routes
Route::get('/merchant/me', [MerchantController::class, 'merchant'])->name('merchant.merchant');
Route::put('/merchant/update', [MerchantController::class, 'update'])->name('merchant.update');

Route::post('/merchant/logout', [MerchantController::class, 'logout']);


Route::post('/merchant/category', [CategoryController::class, 'store'])->name('merchant.createcategory');
Route::get('/merchant/category', [CategoryController::class, 'index'])->name('merchant.getcategories');
Route::put('/merchant/category/{categoryId}', [CategoryController::class, 'update'])->name('merchant.updatecategory');

Route::get('/merchant/category/categoryname', [CategoryController::class, 'getByCategoryName'])->name('merchant.getcategoriesbyname');
Route::get('/merchant/category/categorytype', [CategoryController::class, 'getByCategoryType'])->name('merchant.getcategoriesbytype');

});
