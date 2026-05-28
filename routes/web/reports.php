<?php

use App\Http\Controllers\ReportController;

Route::name('reports.')->group(function() {
    Route::get('reports', [ReportController::class, 'index'])->name('index');
    Route::get('reports/export-printers-workplace', [ReportController::class, 'exportPrintersWorkplace'])
        ->name('export-printers-workplace');
    Route::get('reports/export-consumable-count', [ReportController::class, 'exportConsumableCount'])
        ->name('export-consumable-count');
    Route::get('reports/export-consumable-installed-count', [ReportController::class, 'exportConsumableInstalledCount'])
        ->name('export-consumable-installed-count');
});
