<?php

use App\Http\Controllers\ReportController;

Route::name('reports.')->group(function() {
    Route::get('reports', [ReportController::class, 'index'])->name('index');
    Route::post('reports/export-printers-workplace', [ReportController::class, 'exportPrintersWorkplace'])
        ->name('export-printers-workplace');
    Route::post('reports/export-consumable-count', [ReportController::class, 'exportConsumableCount'])
        ->name('export-consumable-count');
    Route::post('reports/export-consumable-installed-count', [ReportController::class, 'exportConsumableInstalledCount'])
        ->name('export-consumable-installed-count');
});
