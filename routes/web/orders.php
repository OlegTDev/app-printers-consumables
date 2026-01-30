<?php

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderSparePartDetailsController;
use App\Http\Controllers\Order\OrderStatusHistoryController;

// Order
Route::put('orders/{order}/approve', [OrderController::class, 'approve']);
Route::put('orders/{order}/reject', [OrderController::class, 'reject']);
Route::put('orders/{order}/completed', [OrderController::class, 'completed']);
Route::put('orders/{order}/cancel', [OrderController::class, 'cancel']);
Route::delete('orders/{order}', [OrderController::class, 'destroy']);

// OrderSpareParts
Route::resource('orders/spare-parts', OrderSparePartDetailsController::class)
    ->parameters([
        'spare-parts' => 'orderSparePartDetails',
    ]);
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
