<?php

use App\Http\Controllers\PrintersWorkplaceController;

Route::get('printers/workplace/all', [PrintersWorkplaceController::class, 'all']);
Route::resource('printers/workplace', PrintersWorkplaceController::class);
Route::get('printers/workplace/list/{consumable}', [PrintersWorkplaceController::class, 'list']);
Route::get('printers/workplace/consumables-installed/{workplace}', [PrintersWorkplaceController::class, 'consumablesInstalled']);
