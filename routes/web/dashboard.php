<?php

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/', [DashboardController::class, 'index'])
    ->name('home');
