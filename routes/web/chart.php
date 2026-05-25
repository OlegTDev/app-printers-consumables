<?php

use App\Http\Controllers\ChartController;

Route::get('chart/last', [ChartController::class, 'last']);
Route::get('chart/last-added', [ChartController::class, 'lastAdded'])->name('chart.last-added');
Route::get('chart/last-installed', [ChartController::class, 'lastInstalled'])->name('chart.last-installed');
