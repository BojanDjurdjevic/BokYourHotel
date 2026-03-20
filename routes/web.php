<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomSetupController;
use App\Http\Controllers\Supplier\HotelController;
use App\Http\Controllers\Supplier\HotelSetupController;
use App\Http\Controllers\Supplier\RoomController;
use App\Http\Controllers\Supplier\RoomInventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Middleware\SuperMiddleware;
use App\Http\Middleware\SuppMidleware;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::middleware( ['auth', 'role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
        Route::controller(SupplierController::class)->group(function() {
            Route::get('/dashboard', 'index')
            ->name('dashboard');
            
            
        });
        Route::get('/myhotels', function() {
             return view('supplier.hotels.index');
        })->name('myhotels');
        Route::get('/bookings', function() {
             return view('supplier.bookings.confirmed');
        })->name('bookings');
        Route::get('/pending', function() {
             return view('supplier.bookings.pending');
        })->name('pending');
        Route::get('/revenue', function() {
             return view('supplier.revenue');
        })->name('revenue');

        //Setup wizard routes:

        Route::get(
            '/hotels/{hotel}/setup',
            [HotelSetupController::class,'info']
        )->name('hotels.setup.info');

        Route::get(
            '/hotels/{hotel}/setup/rooms',
            [HotelSetupController::class,'rooms']
        )->name('hotels.setup.rooms');

        Route::get(
            '/hotels/{hotel}/setup/inventory',
            [HotelSetupController::class,'inventory']
        )->name('hotels.setup.inventory');

        // Calendar

        Route::get(
        '/hotels/{hotel}/inventory-calendar',
        [RoomInventoryController::class,'index']
        )->name('inventory.calendar');

        Route::get(
        '/hotels/{hotel}/inventory-calendar/data',
        [RoomInventoryController::class,'monthData']
        )->name('inventory.calendar.data');

        Route::post(
            '/hotels/{hotel}/inventory',
            [HotelSetupController::class,'storeInventory']
        )->name('hotels.inventory.store');

        Route::post(
        '/inventory/update-day',
        [RoomInventoryController::class,'updateDay']
        )->name('inventory.update');

        // Images

        //Route::livewire('hotels/{hotel}/setup/images', 'hotel-images-manager');

        Route::get(
            '/hotels/{hotel}/setup/images',
            [HotelSetupController::class,'images']
        )->name('hotels.setup.images'); 
        

        Route::get(
            '/hotels/{hotel}/setup/publish',
            [HotelSetupController::class,'publish']
        )->name('hotels.setup.publish');

        Route::put(
            '/hotels/{hotel}/setup/publish-my-hotel',
            [HotelSetupController::class, 'publishHotel']
        )->name('hotels.setup.publishHotel');

        

        //Resource:

        Route::resource('hotels', HotelController::class);

        Route::resource('hotels.rooms', RoomController::class);

        // NEW ROOM ROUTES
        Route::get(
            '/rooms/{room}/images/index', [RoomSetupController::class, 'images']
        )->name('rooms.images.index');

        Route::post('/rooms/{room}/images/store', [RoomSetupController::class, 'storeImages'])
        ->name('rooms.images.store');

        Route::get(
            '/rooms/{room}/facilities', [RoomSetupController::class, 'facilities']
        )->name('rooms.facilities');

        Route::put(
            '/rooms/{room}/facilities-update', [RoomSetupController::class, 'facilitiesUpdate']
        )->name('rooms.facilities.update');
            
        Route::get(
            '/rooms/{room}/inventory/index', [RoomSetupController::class, 'inventory']
        )->name('rooms.inventory');

        Route::put(
            '/rooms/{room}/inventory-update', [RoomSetupController::class, 'inventoryUpdate']
        )->name('rooms.inventory.update');
    });

    Route::middleware(SuperMiddleware::class)->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('layouts.dashboard');
        })->name('admin.dashboard');
    }); 
});

require __DIR__.'/auth.php';
