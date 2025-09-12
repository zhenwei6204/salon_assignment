<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'home']);
Route::get('/about', fn () => view('about'));
Route::get('/contact', fn () => view('contact'));

/*
|--------------------------------------------------------------------------
| Categories & Services
|--------------------------------------------------------------------------
*/
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

/*
|--------------------------------------------------------------------------
| Booking flow (protected)
| NOTE: these routes are under the "booking." name prefix
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('booking')->name('booking.')->group(function () {
    // Step 1: select stylist for a service
    Route::get('/service/{service}/stylists', [BookingController::class, 'selectStylist'])
        ->name('select.stylist');

    // Step 2: choose time
    Route::get('/service/{service}/stylist/{stylist}/times', [BookingController::class, 'chooseTime'])
        ->name('select.time');

    // Step 3: confirmation screen
    Route::get('/service/{service}/stylist/{stylist}/confirmation', [BookingController::class, 'confirm'])
        ->name('confirmation');

    // Step 4: store booking (creates booking + payment record, then redirects to payment page)
    Route::post('/store', [BookingController::class, 'store'])->name('store');

    // Step 5: Payment routes (separate from booking creation)
    Route::prefix('payment')->name('payment.')->group(function() {
        // Show payment page
        Route::get('/{serviceId}', [PaymentController::class, 'makePayment'])
            ->name('makePayment');
        
        // Process payment
        Route::post('/{serviceId}/process', [PaymentController::class, 'processPayment'])
            ->name('process');
    });

    // Step 6: Success page (called after successful payment)
    Route::get('/success/{id}', [BookingController::class, 'success'])->name('success');

    // Cancel a booking
    Route::patch('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
});

/*
|--------------------------------------------------------------------------
| My Bookings (protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/my-bookings', [BookingController::class, 'myBookings'])
    ->name('bookings.index');  


    
Route::middleware(['auth'])->get('/payment-history', [PaymentController::class, 'paymentHistory'])
    ->name('payments.history');

/*--------------------------------------------------------------------------
| Optional shortcut (protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/services/{service}/book', [BookingController::class, 'selectStylist'])
    ->name('booking.stylists');

/*
|--------------------------------------------------------------------------
| Jetstream dashboard
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
});