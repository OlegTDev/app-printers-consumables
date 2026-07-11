<?php

use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersOrganizationsController;


Route::get('users/organizations', [UsersOrganizationsController::class, 'index'])
    ->name('users.organizations');

Route::post('users/organizations/{organization}', [UsersOrganizationsController::class, 'change'])
    ->name('users.organizations.change');

Route::resource('users', UsersController::class)
    ->only(['edit'])
    ->withTrashed(['edit']);

Route::middleware('role:admin')->group(function () {
    Route::resource('users', UsersController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy'])
        ->withTrashed();

    Route::put('users/{user}/restore', [UsersController::class, 'restore'])
        ->name('users.restore')
        ->withTrashed();
});
