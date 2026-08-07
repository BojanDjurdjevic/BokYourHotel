<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth','role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/admin/dashboard', function () {
        return view('layouts.dashboard');
    })->name('admin.dashboard');

});