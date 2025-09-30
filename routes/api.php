<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// Authentication
Route::post('/login', [AuthController::class,'login'] );




// Products
Route::post('/create_product', [ProductController::class,'create'])->middleware(('auth:sanctum'));
Route::get('/get_product', [ProductController::class,'index'])->middleware(('auth:sanctum'));
Route::get('/each_product/{id}', [ProductController::class,'show'])->middleware(('auth:sanctum'));
Route::delete('/delete_product/{id}', [ProductController::class,'destroy'])->middleware(('auth:sanctum'));
Route::post('/products/update/{id}', [ProductController::class, 'update']);
Route::get('/products/search/{name}', [ProductController::class, 'search']);



// order
Route::post('/order', [PurchaseController::class,'store'])->middleware(('auth:sanctum'));
Route::get('/allorder', [PurchaseController::class,'allOrders'])->middleware(('auth:sanctum'));
Route::get('/todayorder', [PurchaseController::class,'todaysOrders'])->middleware(('auth:sanctum'));
Route::get('/orders/month/{month}/{year?}', [PurchaseController::class, 'ordersByMonth']);


// Admin actions
Route::put('/sales-reps/{id}', [AuthController::class, 'updateSalesRep'])->middleware('auth:sanctum');
