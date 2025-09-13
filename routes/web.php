<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\PaymentController;
use App\Models\Stylist;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StylistDashboardController;
use App\Models\Service;
use App\Http\Controllers\StylistController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
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


    Route::middleware(['auth'])->prefix('refunds')->name('refunds.')->group(function () {
    Route::get('/', [RefundController::class, 'refund'])->name('refund');
    Route::get('/create', [RefundController::class, 'create'])->name('create');
    Route::post('/store', [RefundController::class, 'store'])->name('store');
    Route::get('/{refund}', [RefundController::class, 'show'])->name('show');
    Route::patch('/{refund}/cancel', [RefundController::class, 'cancel'])->name('cancel');
});
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

/*
|--------------------------------------------------------------------------
| Users Site
|--------------------------------------------------------------------------
*/
// Edit Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.page');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit'); // edit page
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update'); // form submission
});

// Login to dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $services = Service::all(); // or filter for featured services
        return view('home', compact('services')); // pass $services to the view
    })->name('dashboard');
});

Route::post('/stylists/{stylist}/reviews', [ReviewController::class, 'store'])
    ->middleware('auth') // only logged-in users can review
    ->name('reviews.store');


/*
|--------------------------------------------------------------------------
| Stylist Site
|--------------------------------------------------------------------------
*/
// Stylists list
Route::get('/stylists', [StylistController::class, 'index'])->name('stylist.index');

// Individual stylist profile (with route-model binding)
Route::get('/stylists/{stylist}', [StylistController::class, 'show'])->name('stylists.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/stylist/dashboard', [StylistDashboardController::class, 'index'])
         ->name('stylist.dashboard');

    Route::put('/stylist/profile', [StylistDashboardController::class, 'updateProfile'])
         ->name('stylist.profile.update');

    Route::put('/stylist/schedule', [StylistDashboardController::class, 'updateSchedule'])
         ->name('stylist.schedule.update');
});

/*
|--------------------------------------------------------------------------
| Admin Logout Site
|--------------------------------------------------------------------------
*/
Route::get('/test-logout-binding', function() {
    $logoutResponse = app(\Filament\Http\Responses\Auth\Contracts\LogoutResponse::class);
    dd(get_class($logoutResponse));
});

if(app()->environment('local')){
    Route::get('/test-login/{id}', function($id){
        $user = User::find($id);
        if($user){
            Auth::login($user);
            return redirect()->route('stylist.dashboard');
        }
        return 'User not found';
    });
    }