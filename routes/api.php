<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\UserApiController;

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
| Service API Routes - Public Access (for your teammates)
|--------------------------------------------------------------------------
*/

// Service CRUD operations
Route::prefix('services')->group(function () {
    // GET /api/services - List all services with filters
    Route::get('/', [ServiceApiController::class, 'index']);
    
    // GET /api/services/categories - Get all service categories (MUST come before {id} route)
    Route::get('/categories', [ServiceApiController::class, 'getCategories']);
    
    // GET /api/services/by-duration/{duration} - Get services by max duration
    Route::get('/by-duration/{duration}', [ServiceApiController::class, 'getByDuration']);
    
    // GET /api/services/by-price?min=50&max=100 - Get services by price range
    Route::get('/by-price', [ServiceApiController::class, 'getByPriceRange']);
    
    // GET /api/services/{id} - Get specific service (MUST come last among GET routes)
    Route::get('/{id}', [ServiceApiController::class, 'show']);
    
    // POST /api/services - Create new service (protected)
    Route::post('/', [ServiceApiController::class, 'store'])->middleware('auth:sanctum');
    
    // PUT /api/services/{id} - Update service (protected)
    Route::put('/{id}', [ServiceApiController::class, 'update'])->middleware('auth:sanctum');
    
    // DELETE /api/services/{id} - Delete service (protected)
    Route::delete('/{id}', [ServiceApiController::class, 'destroy'])->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| API Documentation Route
|--------------------------------------------------------------------------
*/
Route::get('/docs', function () {
    return response()->json([
        'service' => 'Salon Service API',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /api/services' => 'List all services with optional filters',
            'GET /api/services/{id}' => 'Get specific service details',
            'POST /api/services' => 'Create new service (requires authentication)',
            'PUT /api/services/{id}' => 'Update service (requires authentication)',
            'DELETE /api/services/{id}' => 'Delete service (requires authentication)',
            'GET /api/services/by-duration/{duration}' => 'Get services by maximum duration',
            'GET /api/services/by-price' => 'Get services by price range (query: min, max)',
            'GET /api/services/categories' => 'Get all service categories',
        ],
        'filters' => [
            'available' => 'boolean - Filter by availability',
            'category_id' => 'integer - Filter by category ID',
            'min_price' => 'decimal - Minimum price filter',
            'max_price' => 'decimal - Maximum price filter',
            'min_duration' => 'integer - Minimum duration in minutes',
            'max_duration' => 'integer - Maximum duration in minutes',
            'search' => 'string - Search in name, description, benefits',
            'per_page' => 'integer - Items per page (default: 15)'
        ]
    ]);
});


Route::prefix('v1')->group(function () {
    Route::get('/items',                        [InventoryApiController::class, 'indexItems']);
    Route::get('/items/{id}',                   [InventoryApiController::class, 'showItem']);
    Route::get('/services/{id}/requirements',   [InventoryApiController::class, 'requirements']);
    Route::get('/services/{id}/stock-check',    [InventoryApiController::class, 'stockCheck']);
    Route::post('/inventory/reserve',           [InventoryApiController::class, 'reserveForBooking']);
    // GET /api/services/{id} - Get specific service (MUST come last among GET routes)
    Route::get('/{id}', [ServiceApiController::class, 'show']);
    
    // POST /api/services - Create new service (protected)
    Route::post('/', [ServiceApiController::class, 'store'])->middleware('auth:sanctum');
    
    // PUT /api/services/{id} - Update service (protected)
    Route::put('/{id}', [ServiceApiController::class, 'update'])->middleware('auth:sanctum');
    
    // DELETE /api/services/{id} - Delete service (protected)
    Route::delete('/{id}', [ServiceApiController::class, 'destroy'])->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| API Documentation Route
|--------------------------------------------------------------------------
*/
Route::get('/docs', function () {
    return response()->json([
        'service' => 'Salon Service API',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /api/services' => 'List all services with optional filters',
            'GET /api/services/{id}' => 'Get specific service details',
            'POST /api/services' => 'Create new service (requires authentication)',
            'PUT /api/services/{id}' => 'Update service (requires authentication)',
            'DELETE /api/services/{id}' => 'Delete service (requires authentication)',
            'GET /api/services/by-duration/{duration}' => 'Get services by maximum duration',
            'GET /api/services/by-price' => 'Get services by price range (query: min, max)',
            'GET /api/services/categories' => 'Get all service categories',
        ],
        'filters' => [
            'available' => 'boolean - Filter by availability',
            'category_id' => 'integer - Filter by category ID',
            'min_price' => 'decimal - Minimum price filter',
            'max_price' => 'decimal - Maximum price filter',
            'min_duration' => 'integer - Minimum duration in minutes',
            'max_duration' => 'integer - Maximum duration in minutes',
            'search' => 'string - Search in name, description, benefits',
            'per_page' => 'integer - Items per page (default: 15)'
        ]
    ]);
});
/*
|--------------------------------------------------------------------------
| Health Check Route
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'Service API',
        'timestamp' => now()->toISOString(),
        'timezone' => config('app.timezone')
    ]);
});


/*
|--------------------------------------------------------------------------
| Check Stock Availability 
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','throttle:60,1'])->group(function () {
    Route::get('/inventory/requirements', [InventoryAvailabilityController::class, 'requirements']);
    Route::get('/inventory/availability', [InventoryAvailabilityController::class, 'availability']);
});







/*
|--------------------------------------------------------------------------
| Check Servivce 
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::get('/services', [ServiceApiController::class, 'index']);
    Route::get('/services/{service}', [ServiceApiController::class, 'show']);
}); 



/*
|--------------------------------------------------------------------------
| Health Check Route
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'Service API',
        'timestamp' => now()->toISOString(),
        'timezone' => config('app.timezone')
    ]);
});


/*
|--------------------------------------------------------------------------
| Check Stock Availability 
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum','throttle:60,1'])->group(function () {
    Route::get('/inventory/requirements', [InventoryAvailabilityController::class, 'requirements']);
    Route::get('/inventory/availability', [InventoryAvailabilityController::class, 'availability']);
});







/*
|--------------------------------------------------------------------------
| Check Servivce 
|--------------------------------------------------------------------------
*/
Route::prefix('api/v1')
    ->middleware(['service.key','throttle:60,1'])   // or 'auth:sanctum'
    ->group(function () {
        Route::get('/services', [ServiceApiController::class, 'index']);
        Route::get('/services/{service}', [ServiceApiController::class, 'show']);
    });
