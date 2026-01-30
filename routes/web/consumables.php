<?php

use App\Http\Controllers\ConsumablesCountsAddedController;
use App\Http\Controllers\ConsumablesCountsController;
use App\Http\Controllers\ConsumablesCountsInstalledController;

Route::resource('consumables/counts', ConsumablesCountsController::class)->only(['index', 'create', 'store', 'show', 'update']);
Route::post('consumables/counts/{count}/correct', [ConsumablesCountsController::class, 'correctCount'])->middleware('role:admin,subtract-consumable');
Route::post('consumables/counts/validate', [ConsumablesCountsController::class, 'validateConsumableCount']);
Route::post('consumables/counts/check-exists', [ConsumablesCountsController::class, 'checkExists']);
Route::put('consumables/counts/{count}/update-organizations', [ConsumablesCountsController::class, 'updateOrganizations'])
    ->middleware('role:admin,add-consumables');
Route::get('consumables/counts/{count}/journal-added', [ConsumablesCountsController::class, 'journalAdded']);
Route::get('consumables/counts/{count}/journal-installed', [ConsumablesCountsController::class, 'journalInstalled']);
Route::get('consumables/counts/list-by-printer/{printer}', [ConsumablesCountsController::class, 'listByPrinter']);

Route::resource('consumables.counts.added', ConsumablesCountsAddedController::class)->only(['index', 'destroy']);
Route::resource('consumables.counts.installed', ConsumablesCountsInstalledController::class)->only(['index', 'store', 'destroy']);
Route::get('consumables/counts/installed/last', [ConsumablesCountsInstalledController::class, 'last']);
Route::get('consumables/counts/installed/master', [ConsumablesCountsInstalledController::class, 'master']);
