<?php

use App\Http\Controllers\Api\ConsumableApiController;
use App\Http\Controllers\Dictionary\ConsumablesController;
use App\Http\Controllers\Dictionary\ConsumablesPrintersController;
use App\Http\Controllers\Dictionary\PrintersConsumablesController;
use App\Http\Controllers\Dictionary\PrintersController;
use App\Http\Controllers\Dictionary\OrganizationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('dictionary')->name('dictionary.')->group(function() {

    // принтеры
    Route::resource('printers', PrintersController::class);
    Route::middleware('role:admin,editor-dictionary')->group(function() {
        Route::resource('printers.consumables', PrintersConsumablesController::class)->only(['index', 'destroy']);
        Route::post('/printers/{printer}/consumables/{consumable}/add', [PrintersConsumablesController::class, 'add'])
            ->name('printers.consumables.add');
    });

    // Расходные материалы
    Route::get('/consumables/not-other', [ConsumableApiController::class, 'notOtherConsumablesForPrinter'])
        ->name('consumables.not-other');
    Route::get('/consumables/{printer}/other', [ConsumableApiController::class, 'otherConsumablesForPrinter'])
        ->name('consumables.other');
    Route::resource('consumables', ConsumablesController::class);
    Route::middleware('role:admin,editor-dictionary')->group(function() {
        Route::resource('consumables.printers', ConsumablesPrintersController::class)->only(['index', 'destroy']);
        Route::post('/consumables/{consumable}/printers/{printer}/add', [ConsumablesPrintersController::class, 'add'])
            ->name('consumables.printers.add');
    });

    // Организации
    Route::resource('organizations', OrganizationsController::class)->middleware('role:admin');

});
