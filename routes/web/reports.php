<?php

use App\Http\Controllers\ReportController;

Route::get('reports', [ReportController::class, 'index']);
Route::post('reports/export-printers-workplace', [ReportController::class, 'exportPrintersWorkplace']);
Route::post('reports/export-consumable-count', [ReportController::class, 'exportConsumableCount']);
Route::post('reports/export-consumable-installed-count', [ReportController::class, 'exportConsumableInstalledCount']);
