<?php

use App\Http\Controllers\Dictionary\ConsumablesController;
use App\Http\Controllers\Dictionary\ConsumablesPrintersController;
use App\Http\Controllers\Dictionary\PrintersConsumablesController;
use App\Http\Controllers\Dictionary\PrintersController;
use App\Http\Controllers\Dictionary\OrganizationsController;
use App\Http\Controllers\Dictionary\SparePartsController;
use Illuminate\Support\Facades\Route;

Route::prefix('dictionary')->name('dictionary.')->group(function() {

    // принтеры
    Route::resource('printers', PrintersController::class);
    Route::middleware('role:admin,editor-dictionary')->group(function() {
        Route::resource('printers.consumables', PrintersConsumablesController::class)->only(['index', 'destroy']);
        Route::post('/printers/{printer}/consumables/{consumable}/add', [PrintersConsumablesController::class, 'add']);
    });

    // Расходные материалы
    Route::resource('consumables', ConsumablesController::class);
    Route::middleware('role:admin,editor-dictionary')->group(function() {
        Route::resource('consumables.printers', ConsumablesPrintersController::class)->only(['index', 'destroy']);
        Route::post('/consumables/{consumable}/printers/{printer}/add', [ConsumablesPrintersController::class, 'add']);
    });
    Route::get('/consumables/{printer}/other', [ConsumablesController::class, 'otherConsumablesForPrinter']);

    // Организации
    Route::resource('organizations', OrganizationsController::class)->middleware('role:admin');

});
