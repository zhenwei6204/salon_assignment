<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController; 
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookingController;



// Categories page
Route::get('/categories', function () {
    return view('categories');
});

// About page
Route::get('/about', function () {
    return view('about');
});

// Contact page
Route::get('/contact', function () {
    return view('contact');
});
Route::get('/', [App\Http\Controllers\HomeController::class, 'home']); 

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

// Booking routes
Route::prefix('booking')->name('booking.')->group(function () {
    // Step 1: Select stylist
    Route::get('/service/{service}/stylists', [BookingController::class, 'selectStylist'])
        ->name('select.stylist');
    
    // Step 2: Select time
    Route::get('/service/{service}/stylist/{stylist}/times', [BookingController::class, 'selectTime'])
        ->name('select.time');
    
    // Step 3: Show confirmation
    Route::get('/service/{service}/stylist/{stylist}/confirmation', [BookingController::class, 'confirmation'])
        ->name('confirmation');
    
    // Step 4: Store booking
    Route::post('/store', [BookingController::class, 'store'])
        ->name('store');
    
    // Success page
    Route::get('/success/{booking}', [BookingController::class, 'success'])
        ->name('success');
});

// Route for selecting a stylist
Route::get('/services/{service}/book', [BookingController::class, 'selectStylist'])->name('booking.stylists');





Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
