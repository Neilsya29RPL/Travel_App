<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BookingController;

Route::get('/', function () {
    return view('landing');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Preferences
Route::middleware('auth')->group(function () {
    // Preferences (single form)
    Route::get('/preferences', [PreferenceController::class, 'index'])->name('preferences.index');
    Route::post('/preferences', [PreferenceController::class, 'store'])->name('preferences.store');

    // Recommendations (hapus halaman index)
    Route::post('/recommendations/run', [RecommendationController::class, 'run'])->name('recommendations.run');
    Route::match(['get','post'], '/recommendations/accommodations', [RecommendationController::class, 'accommodations'])->name('recommendations.accommodations');


    // Budget
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');

    // Booking & Payment
    Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/bookings', [BookingController::class, 'list'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/booking', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/payment', [BookingController::class, 'pay'])->name('payment.pay');
    Route::get('/payment/checkout', [BookingController::class, 'checkout'])->name('payment.checkout');
});
