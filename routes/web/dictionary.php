<?php

use App\Http\Controllers\Api\ConsumableApiController;
use App\Http\Controllers\Dictionary\ConsumablesController;
use App\Http\Controllers\Dictionary\ConsumablesPrintersController;
use App\Http\Controllers\Dictionary\PrintersConsumablesController;
use App\Http\Controllers\Dictionary\PrintersController;
use App\Http\Controllers\Dictionary\OrganizationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('dictionary')->name('dictionary.')->group(function() {
    // Организации
    Route::resource('organizations', OrganizationsController::class)->middleware('role:admin');

    // === ГРУППА ДЛЯ АДМИНИСТРАТОРОВ И РЕДАКТОРОВ СПРАВОЧНИКОВ ===
    Route::middleware('role:admin,editor-dictionary')->group(function () {
        // принтеры
        Route::resource('printers', PrintersController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('printers.consumables', PrintersConsumablesController::class)->only(['index', 'destroy']);
        Route::post('/printers/{printer}/consumables/{consumable}/add', [PrintersConsumablesController::class, 'add'])
            ->name('printers.consumables.add');

        // // расходные материалы
        Route::resource('consumables', ConsumablesController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('consumables.printers', ConsumablesPrintersController::class)->only(['index', 'store', 'destroy']);
    });

    // Расходные материалы
    Route::get('/consumables/not-other', [ConsumableApiController::class, 'notOtherConsumablesForPrinter'])
        ->name('consumables.not-other');
    Route::get('/consumables/{printer}/other', [ConsumableApiController::class, 'otherConsumablesForPrinter'])
        ->name('consumables.other');

    // === ОБЩЕДОСТУПНЫЕ МАРШРУТЫ СПРАВОЧНИКОВ (Чтение) ===
    Route::resource('printers', PrintersController::class)->only(['index', 'show']);
    Route::resource('consumables', ConsumablesController::class)->only(['index', 'show']);


});
