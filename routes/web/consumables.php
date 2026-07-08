<?php

use App\Http\Controllers\Api\ConsumableApiController;
use App\Http\Controllers\ConsumablesCountsAddedController;
use App\Http\Controllers\ConsumablesCountsController;
use App\Http\Controllers\ConsumablesCountsInstalledController;

Route::resource('consumables/counts', ConsumablesCountsController::class)
    ->names('consumables.counts')
    ->only(['index', 'create', 'store', 'show', 'update']);
Route::post('consumables/counts/{count}/correct', [ConsumablesCountsController::class, 'correctCount'])
    ->middleware('role:admin,subtract-consumable')
    ->name('consumables.counts.correct');
Route::post('consumables/counts/validate', [ConsumablesCountsController::class, 'validateConsumableCount'])
    ->name('consumables/counts/validate');
Route::get('consumables/counts/{idConsumable}/exists', [ConsumablesCountsController::class, 'exists'])
    ->name('consumables.counts.exists');
Route::put('consumables/counts/{count}/update-organizations', [ConsumablesCountsController::class, 'updateOrganizations'])
    ->middleware('role:admin,add-consumables')
    ->name('consumables.counts.update-organization');
Route::get('consumables/counts/{count}/journal-added', [ConsumablesCountsController::class, 'journalAdded']);
Route::get('consumables/counts/{count}/journal-installed', [ConsumablesCountsController::class, 'journalInstalled']);
Route::get('consumables/counts/list-by-printer/{printer}', [ConsumablesCountsController::class, 'listByPrinter'])
    ->name('consumables.counts.list-by-printer');

Route::scopeBindings()->group(function () {
    Route::resource('consumables.counts.added', ConsumablesCountsAddedController::class)
        ->only(['index', 'destroy']);
    Route::resource('consumables.counts.installed', ConsumablesCountsInstalledController::class)->only(['index', 'store', 'destroy']);
});
Route::get('consumables/counts/installed/last', [ConsumableApiController::class, 'lastConsumableCountInstalled'])
    ->name('consumables.counts.installed.last');
