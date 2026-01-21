<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ConsumablesCountsAddedController;
use App\Http\Controllers\ConsumablesCountsController;
use App\Http\Controllers\ConsumablesCountsInstalledController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\Order\OrderSparePartDetailsController;
use App\Http\Controllers\PrintersWorkplaceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersOrganizationsController;
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

// Auth
Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');



// Authenticate middleware
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Users
    Route::resource('users', UsersController::class)->except(['show'])->withTrashed(['edit']);
    Route::put('users/{user}/restore', [UsersController::class, 'restore'])
        ->name('users.restore')
        ->withTrashed()
        ->middleware('role:admin');

    Route::get('users/organizations', [UsersOrganizationsController::class, 'index'])
        ->name('users.organizations');
    Route::post('users/organizations/{organization}', [UsersOrganizationsController::class, 'change']);

    // Printers
    Route::get('printers/workplace/all', [PrintersWorkplaceController::class, 'all']);
    Route::resource('printers/workplace', PrintersWorkplaceController::class);
    Route::get('printers/workplace/list/{consumable}', [PrintersWorkplaceController::class, 'list']);
    Route::get('printers/workplace/consumables-installed/{workplace}', [PrintersWorkplaceController::class, 'consumablesInstalled']);

    // ConsumableCount    
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

    // Chart
    Route::get('chart/last', [ChartController::class, 'last']);
    Route::get('chart/last-added', [ChartController::class, 'lastAdded']);
    Route::get('chart/last-installed', [ChartController::class, 'lastInstalled']);

    // Reports
    Route::get('reports', [ReportController::class, 'index']);
    Route::post('reports/export-printers-workplace', [ReportController::class, 'exportPrintersWorkplace']);
    Route::post('reports/export-consumable-count', [ReportController::class, 'exportConsumableCount']);
    Route::post('reports/export-consumable-installed-count', [ReportController::class, 'exportConsumableInstalledCount']);

    // Orders
    Route::resource('orders/spare-parts', OrderSparePartDetailsController::class)->withTrashed(['edit']);

    // Images
    Route::get('/img/{path}', [ImagesController::class, 'show'])
        ->where('path', '.*')
        ->name('image');

});




require_once __DIR__ . "/dictionary.php";
