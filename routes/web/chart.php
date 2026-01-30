<?php

use App\Http\Controllers\ChartController;

Route::get('chart/last', [ChartController::class, 'last']);
Route::get('chart/last-added', [ChartController::class, 'lastAdded']);
Route::get('chart/last-installed', [ChartController::class, 'lastInstalled']);
