<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicHotelController;

Route::get('/hotels', [PublicHotelController::class, 'index'])->name('hotels.index');
Route::get('/destinations', \App\Http\Controllers\DestinationController::class)->middleware('throttle:availability')->name('destinations.index');
Route::get('/hotels/{hotel}', [PublicHotelController::class, 'show'])->name('hotels.show');

Route::get('/', function (\App\Http\Requests\HotelSearchRequest $request) {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/booking.php';
require __DIR__.'/supplier.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
