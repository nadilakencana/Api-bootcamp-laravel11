<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SalesController;
use App\Models\Category;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

//   Route::get('/get-user', [AuthController::class, 'user']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['api'])->group(function (){
    // category customer
    Route::get('category', [CategoryController::class, 'getCategory']);

    // Product Customer
    Route::controller(ProductController::class)->group(function(){
        Route::get('get-Product', 'getProduct_data');
        Route::get('detail-product/{slug}', 'getProduct_Detail_customer');
        Route::get('product-by-category/{slug}', 'getProduct_byCategory');
    });

    Route::controller(SalesController::class)->group(function(){
        Route::post('customer-Order', 'order');
    });

    // api Admin Auth
    Route::middleware(['auth:sanctum','Auth.auth'])->group(function(){
        // APi category
        Route::controller(CategoryController::class)->group(function(){
            Route::get('get-category', 'getCategory');
            Route::post('create-data-category', 'CreateCategory');
            Route::post('update-category/{slug}', 'UpdateDataCategory');
            Route::delete('delete-data-category/{slug}', 'DeleteDataCategory');
        });

        // api product

        Route::controller(ProductController::class)->group(function(){
            Route::get('data-product/admin', 'getProduct_data');
            Route::post('create-product', 'CreateDataProdut');
            Route::post('update-product/{slug}', 'UpdateDataProdut');
            Route::delete('delete-product/{slug}', 'deleteProduct');
        });


        Route::controller(PaymentController::class)->group(function(){
            Route::get('get-Payment', 'getPayment');
            Route::post('create-payment', 'Createpayment');
            Route::put('Update-payment/{id}', 'upadatePayment');
            Route::delete('delete-data-payment/{id}', 'DeletePayment');
        });

        Route::controller(SalesController::class)->group(function(){
            // Route::get('code-order/{length}', 'code_order');
            Route::post('create-Order', 'order');
            Route::get('detail-order/{code}','Detailorder');
            Route::post('payment/{code}', 'payment_order');
            Route::post('modify/order/admin', 'modify_order');
            Route::post('modify/delete/item-order', 'modify_delete_item');

            Route::get('report/order/daily', 'dailyReports');
            Route::get('report/order/weekly', 'weeklyReports');
            Route::get('report/order/monthly', 'monthlyReports');

        });

    });


});



