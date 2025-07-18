<?php

use App\Http\Controllers\MerchantController;

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;


use Illuminate\Support\Facades\Route;


// Register and Login Routes (No token required here)

Route::controller(SuperAdminController::class)->group(function () {
Route::post('/admin/register', 'register')->name('admin.register');
Route::post('/admin/login', 'login')->name('admin.login');


});

Route::controller(MerchantController::class)->group(function () {
Route::post('/merchant/register', 'register')->name('merchant.register');
Route::post('/merchant/login', 'login')->name('merchant.login');
Route::get('/merchant/all', 'index');

});

// user route without authentication
Route::controller(UserController::class)->group(function () {
Route::post('/user/register', 'register')->name('user.register');
Route::post('/user/login', 'login')->name('user.login');
Route::post('/user/merchants', 'getMerchantsByCategoryName')->name('user.getmerchantsbycategoryname');
Route::get('/user/categories', 'getAllCategories')->name('user.getallcategories');
Route::get('/user/products', 'getProducts')->name('user.getproducts');
Route::get('/user/categorytype', 'getCategoryTypes')->name('user.getCategorytypes');

});



    // Protected Routes (Require token validation)
Route::middleware(['ensure.token.valid'])->group(function () {
    // SuperAdmin Routes
Route::get('/admin/me ', [SuperAdminController::class, 'admin'])->name('admin.admin');
Route::put('/admin/update', [SuperAdminController::class, 'update'])->name('admin.update');
Route::post('/admin/logout', [SuperAdminController::class, 'logout']);
Route::delete('/admin/merchant/{merchantId}', [SuperAdminController::class, 'deleteMerchant'])->name('admin.merchantdelete');
Route::get('/admin/users', [SuperAdminController::class, ''])->name('admin.users');





// Merchant Routes
Route::get('/merchant/me ', [MerchantController::class, 'merchant'])->name('merchant.merchant');
Route::put('/merchant/update', [MerchantController::class, 'update'])->name('merchant.update');

Route::post('/merchant/logout', [MerchantController::class, 'logout']);

Route::post('/merchant/category', [CategoryController::class, 'store'])->name('merchant.createcategory');
Route::get('/merchant/category', [CategoryController::class, 'index'])->name('merchant.getcategories');
Route::put('/merchant/category/{categoryId}', [CategoryController::class, 'update'])->name('merchant.updatecategory');

Route::get('/merchant/category/categoryname', [CategoryController::class, 'getByCategoryName'])->name('merchant.getcategoriesbyname');
Route::get('/merchant/category/categorytype', [CategoryController::class, 'getByCategoryType'])->name('merchant.getcategoriesbytype');

Route::post('/merchant/product', [ProductController::class, 'store'])->name('merchant.createproduct');
Route::get('/merchant/product', [ProductController::class, 'index'])->name('merchant.getproduct');
Route::put('/merchant/product/{productId}', [ProductController::class, 'update'])->name('merchant.updateproduct');
Route::get('/merchant/product/productname', [ProductController::class, 'getByProductName'])->name('merchant.getproductbyname');
Route::get('/merchant/product/producttype', [ProductController::class, 'getByProductType'])->name('merchant.getproductbytype');
Route::delete('/merchant/product/{productId}', [ProductController::class, 'delete'])->name('merchant.deleteproduct');



Route::get('/user', [UserController::class, 'user'])->name('user.getuser');
Route::patch('/user/update', [UserController::class, 'update'])->name('user.updateuser');
Route::post('/cart/add/{productid}', [CartController::class, 'addtocart'])->name('cart.addtocart');
Route::get('/cart/items', [CartController::class, 'viewcart'])->name('cart.viewcart');
Route::patch('/cart/update/{productid}/{cartid}', [CartController::class, 'updatecart'])->name('cart.updatecart');
Route::delete('/cart/remove/{productid}/{cartid}', [CartController::class, 'removefromcart'])->name('cart.removefromcart');
Route::post('/cart/pay', [PaymentController::class, 'initiatePaymnet'])->name('pay.initiatePaymnet');





});

Route::post('/webhook/create', [WebhookController::class, 'handleWebhook'])->name('webhook.handleWebhook');

