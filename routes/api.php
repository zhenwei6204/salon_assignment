<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\InventoryTestController;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Service API Routes - RESTful Routes
|--------------------------------------------------------------------------
*/
Route::prefix('service-data')->group(function () {
    
    // Provide all services to inventory module
    Route::get('/services', [ServiceApiController::class, 'getAllServices']);
    
    // Provide specific service details to inventory module
    Route::get('/services/{id}', [ServiceApiController::class, 'getServiceById']);
    
    // Provide services by category to inventory module
    Route::get('/services/category/{categoryId}', [ServiceApiController::class, 'getServicesByCategory']);
    
    // Provide all categories to inventory module
    Route::get('/categories', [ServiceApiController::class, 'getAllCategories']);
    
    // Provide active/available services only
    Route::get('/services/active', [ServiceApiController::class, 'getActiveServices']);
    
    // Webhook endpoint for inventory when service is updated
    Route::post('/services/{id}/notify-update', [ServiceApiController::class, 'notifyServiceUpdate']);
});


Route::prefix('v1')->group(function () {
    Route::get('/items',                        [InventoryApiController::class, 'indexItems']);
    Route::get('/items/{id}',                   [InventoryApiController::class, 'showItem']);
    Route::get('/services/{id}/requirements',   [InventoryApiController::class, 'requirements']);
    Route::get('/services/{id}/stock-check',    [InventoryApiController::class, 'stockCheck']);
    Route::post('/inventory/reserve',           [InventoryApiController::class, 'reserveForBooking']);
});

/*
|--------------------------------------------------------------------------
| Test Routes for Inventory API Consumption
|--------------------------------------------------------------------------
*/
Route::prefix('test')->group(function () {
    Route::get('/inventory/stock-check/{serviceId}', [InventoryTestController::class, 'testStockCheck']);
    Route::post('/inventory/reservation-test', [InventoryTestController::class, 'testReservation']);
});
/* 
HOW INVENTORY MODULE WILL CONSUME THIS API:

1. Get all services for item linking:
   GET /api/service-data/services
   
2. Get specific service details:
   GET /api/service-data/services/1
   
3. Get services by category:
   GET /api/service-data/services/category/1
   
4. Get all categories:
   GET /api/service-data/categories
   
5. Get only active services:
   GET /api/service-data/services/active

*/