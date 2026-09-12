<?php

use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Booking\BookingManagementController;
use App\Http\Controllers\Booking\GuestBookingController;
use App\Http\Controllers\Booking\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/hotels/{hotel}/booking', [BookingController::class, 'show'])->name('booking.show');

Route::get('/hotels/{hotel}/availability', [BookingController::class, 'availability'])->name('booking.availability');

Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

Route::get('/booking/{booking}/success', [BookingController::class, 'success'])->middleware('signed')->name('booking.success');

// Guest booking management:

Route::middleware('signed')->group(function () {
    Route::get('/guest/bookings/{booking}/checkout', [PaymentController::class, 'show'])->name('guest.payments.show');
    Route::post('/guest/bookings/{booking}/payment', [PaymentController::class, 'submit'])->name('guest.payments.submit');
    Route::post('/guest/bookings/{booking}/payment/retry', [PaymentController::class, 'retry'])->name('guest.payments.retry');
    Route::get('/guest/bookings/{booking}/manage', [GuestBookingController::class, 'show'])->name('guest.bookings.show');
    Route::post('/guest/bookings/{booking}/cancel', [GuestBookingController::class, 'cancel'])->name('guest.bookings.cancel');
});

Route::middleware('auth')->controller(PaymentController::class)->group(function () {
    Route::get('/bookings/{booking}/checkout', 'show')->name('payments.show');
    Route::post('/bookings/{booking}/payment', 'submit')->name('payments.submit');
    Route::post('/bookings/{booking}/payment/retry', 'retry')->name('payments.retry');
});

Route::middleware('auth')->controller(BookingManagementController::class)->group(function () {
    Route::get('/bookings', 'index')->name('bookings.index');
    Route::get('/bookings/{booking}', 'show')->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', 'cancel')->name('bookings.cancel');
    Route::post('/bookings/{booking}/confirm', 'confirm')->name('bookings.confirm');
    Route::post('/bookings/{booking}/complete', 'complete')->name('bookings.complete');
});
