<?php

use App\Http\Controllers\Api\ConsumableApiController;
use App\Http\Controllers\Api\PrintersWorkplaceApiController;
use App\Http\Controllers\PrintersWorkplaceController;


Route::get('printers/workplace/all', [PrintersWorkplaceApiController::class, 'all'])->name('workplace.all');

Route::middleware('role:admin,editor-printer-workplace')->group(function () {
    Route::resource('printers/workplace', PrintersWorkplaceController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
});

Route::resource('printers/workplace', PrintersWorkplaceController::class)->only(['index', 'show']);


Route::middleware('role:admin,subtract-consumable')->group(function () {
    Route::get('printers/workplace/list/{consumable}', [PrintersWorkplaceApiController::class, 'listByConsumable'])
        ->name('printers-workplace.list');
});

Route::get('printers/workplace/consumables-installed/{workplace}', [PrintersWorkplaceApiController::class, 'consumablesInstalledByPrinterWorkplace'])
    ->name('printers.workplace.consumables-installed');
