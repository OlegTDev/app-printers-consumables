<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/web/auth.php';

Route::middleware('auth')->group(function () {

    require __DIR__ . '/web/dashboard.php';
    require __DIR__ . '/web/users.php';
    require __DIR__ . '/web/printers.php';
    require __DIR__ . '/web/consumables.php';
    require __DIR__ . '/web/chart.php';
    require __DIR__ . '/web/reports.php';
    require __DIR__ . '/web/orders.php';
    require __DIR__ . '/web/file.php';
    require __DIR__ . '/web/dictionary.php';
});
