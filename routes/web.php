<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Http\Middleware\RoleMidleware;
use App\Livewire\SupplierDashboard;
use Illuminate\Support\Facades\Route;


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
    
    Route::middleware( RoleMidleware::class)->prefix('supplier')->group(function () {
        Route::controller(SupplierController::class)->group(function() {
            Route::get('/dashboard', 'index')
            ->name('supplier.dashboard');
        });
        
    });

    Route::middleware(['role:admin', RoleMidleware::class])->group(function () {
        Route::get('/admin/dashboard', function () {
            return 'Admin dashboard';
        })->name('admin.dashboard');
    }); 
});

require __DIR__.'/auth.php';
