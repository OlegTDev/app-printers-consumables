<?php

namespace App\Observers;

use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
        $orderStatusHistory = new OrderStatusHistory([
            'status' => $order->status,
            'comment' => $order->comment,
            'id_author' => Auth::id(),
        ]);
        $orderStatusHistory->order()->associate($order);
        $orderStatusHistory->save();        
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $orderOriginal = $order->getOriginal();
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
