<?php

use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersOrganizationsController;

Route::resource('users', UsersController::class)->except(['show'])->withTrashed(['edit']);
Route::put('users/{user}/restore', [UsersController::class, 'restore'])
    ->name('users.restore')
    ->withTrashed()
    ->middleware('role:admin');

Route::get('users/organizations', [UsersOrganizationsController::class, 'index'])
    ->name('users.organizations');
Route::post('users/organizations/{organization}', [UsersOrganizationsController::class, 'change']);