<?php

use App\Http\Controllers\FilesController;

Route::get('/download/{path}', [FilesController::class, 'preview'])
    ->where('path', '.*')
    ->name('download');
