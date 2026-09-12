<?php

use App\Http\Middleware\SuperMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', SuperMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});
