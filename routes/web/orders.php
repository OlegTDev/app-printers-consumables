<?php

use App\Http\Controllers\Order\OrderConsumableDetailsController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderMiscDetailsController;
use App\Http\Controllers\Order\OrderSparePartDetailsController;
use App\Http\Controllers\Order\OrderStatusHistoryController;

Route::name('orders.')->group(function() {
    // Order
    Route::put('orders/{order}/agree', [OrderController::class, 'agree'])->name('agree');
    Route::put('orders/{order}/reject', [OrderController::class, 'reject'])->name('reject');
    Route::put('orders/{order}/ordered', [OrderController::class, 'ordered'])->name('ordered');
    Route::put('orders/{order}/receive', [OrderController::class, 'receive'])->name('receive');
    Route::put('orders/{order}/complete', [OrderController::class, 'complete'])->name('complete');
    Route::put('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('destroy');

    // OrderSpareParts / Заказ запчастей
    Route::resource('orders/spare-parts', OrderSparePartDetailsController::class)
        ->except(['destroy'])
        ->parameters([
            'spare-parts' => 'orderSparePartDetails',
        ]);
    Route::get('orders/spare-parts/{orderSparePartDetails}/files',
        [OrderSparePartDetailsController::class, 'editFiles'])
        ->name('spare-parts.files.edit');
    Route::delete(
        'orders/spare-parts/{orderSparePartDetails}/files/{orderSparePartDetailsFile}',
        [OrderSparePartDetailsController::class, 'deleteFile'])
        ->name('spare-parts.files.delete');
    Route::post(
        'orders/spare-parts/{orderSparePartDetails}/files',
        [OrderSparePartDetailsController::class, 'uploadFiles'])
        ->name('spare-parts.files.upload');

    // OrderStatusHistory
    Route::get('/orders/{order}/status-history', [OrderStatusHistoryController::class, 'index'])
        ->name('status-history');

    // OrderConsumables / Заказ картриджей
    Route::resource('orders/consumables', OrderConsumableDetailsController::class)
        ->except(['destroy'])
        ->parameters([
            'consumables' => 'orderConsumableDetails',
        ]);

    // OrderMisc / Заказ прочих материалов
    Route::resource('orders/misc', OrderMiscDetailsController::class)
        ->except(['destroy'])
        ->parameters([
            'misc' => 'orderMiscDetails',
        ]);
});

