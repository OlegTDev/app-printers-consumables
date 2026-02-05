<?php

use App\Http\Controllers\Order\OrderConsumableDetailsController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderMistDetailsController;
use App\Http\Controllers\Order\OrderSparePartDetailsController;
use App\Http\Controllers\Order\OrderStatusHistoryController;

// Order
Route::put('orders/{order}/agree', [OrderController::class, 'agree']);
Route::put('orders/{order}/reject', [OrderController::class, 'reject']);
Route::put('orders/{order}/ordered', [OrderController::class, 'ordered']);
Route::put('orders/{order}/receive', [OrderController::class, 'receive']);
Route::put('orders/{order}/complete', [OrderController::class, 'complete']);
Route::put('orders/{order}/cancel', [OrderController::class, 'cancel']);
Route::delete('orders/{order}', [OrderController::class, 'destroy']);

// OrderSpareParts
Route::resource('orders/spare-parts', OrderSparePartDetailsController::class)
    ->parameters([
        'spare-parts' => 'orderSparePartDetails',
    ]);
Route::get('orders/spare-parts/{orderSparePartDetails}/files',
    [OrderSparePartDetailsController::class, 'editFiles']);
Route::delete(
    'orders/spare-parts/{orderSparePartDetails}/files/{orderSparePartDetailsFile}',
    [OrderSparePartDetailsController::class, 'deleteFile']
);
Route::post(
    'orders/spare-parts/{orderSparePartDetails}/files',
    [OrderSparePartDetailsController::class, 'uploadFiles']
);

// OrderStatusHistory
Route::get('/orders/{order}/status-history', [OrderStatusHistoryController::class, 'index']);

// OrderConsumables
Route::get('orders/consumables', [OrderConsumableDetailsController::class, 'index']);

// OrderMisc
Route::get('orders/misc', [OrderMistDetailsController::class, 'index']);
