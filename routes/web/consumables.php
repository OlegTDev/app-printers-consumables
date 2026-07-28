<?php

use App\Http\Controllers\Api\ConsumableApiController;
use App\Http\Controllers\ConsumablesCountsAddedController;
use App\Http\Controllers\ConsumablesCountsController;
use App\Http\Controllers\ConsumablesCountsInstalledController;


Route::resource('consumables/counts', ConsumablesCountsController::class)
    ->names('consumables.counts')
    ->only(['index', 'create', 'show']);

Route::post('consumables/counts', [ConsumablesCountsController::class, 'store'])
    ->middleware('role:admin,add-consumables')
    ->name('consumables.counts.store');

Route::put('consumables/counts/{count}', [ConsumablesCountsController::class, 'update'])
    ->middleware(['role:admin,subtract-consumable'])
    ->name('consumables.counts.update');

Route::post('consumables/counts/{count}/correct', [ConsumablesCountsController::class, 'correctCount'])
    ->middleware('role:admin,subtract-consumable')
    ->name('consumables.counts.correct');

Route::put('consumables/counts/{count}/update-organizations', [ConsumablesCountsController::class, 'updateOrganizations'])
    ->middleware('role:admin,add-consumables')
    ->name('consumables.counts.update-organization');

Route::get('consumables/counts/{count}/journal-added', [ConsumablesCountsController::class, 'journalAdded']);

Route::get('consumables/counts/{count}/journal-installed', [ConsumablesCountsController::class, 'journalInstalled']);


// Api start

Route::get('consumables/counts/list-by-printer/{printer}', [ConsumableApiController::class, 'listByPrinter'])
    ->name('consumables.counts.list-by-printer');

Route::get('consumables/counts/by-consumable/{idConsumable}', [ConsumableApiController::class, 'showByConsumable'])
    ->name('consumables.counts.by-consumable');

Route::get('consumables/counts/installed/last', [ConsumableApiController::class, 'lastConsumableCountInstalled'])
    ->name('consumables.counts.installed.last');

// Api end


Route::scopeBindings()->group(function () {
    Route::resource('consumables.counts.added', ConsumablesCountsAddedController::class)
        ->only(['index', 'destroy']);
    Route::resource('consumables.counts.installed', ConsumablesCountsInstalledController::class)->only(['index', 'store', 'destroy']);
});


