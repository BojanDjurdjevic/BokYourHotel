<?php

use App\Http\Controllers\Booking\HomeController;
use App\Http\Controllers\Booking\HotelController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Supplier\HotelController as SupplierHotelController;
use Illuminate\Support\Facades\Route;

/*
Route::controller(HomeController::class)->group(function () {

    Route::get('/', 'index')
        ->name('home');

}); */

Route::controller(SupplierHotelController::class)
    ->prefix('hotels')
    ->name('hotels.')
    ->group(function () {

        Route::get('/', 'index')
            ->name('index');

        Route::get('/{hotel}', 'show')
            ->name('show');
}); 

Route::controller(BookingController::class)
    ->name('booking.')
    ->group(function () {

        Route::get('/hotels/{hotel}/booking', 'show')
            ->name('show');

        Route::post('/booking', 'store')
            ->name('store');

        Route::get('/booking/{booking}/success', 'success')
            ->name('success');

});

