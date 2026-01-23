<?php

namespace App\Observers;

use App\Models\Order\Order;
use App\Models\Order\OrderStatusHistory;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->createStatusHistory($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $orderOriginal = $order->getOriginal();

        if ($orderOriginal['status'] != $order->status) {
            $this->createStatusHistory($order);
        }
    }

    private function createStatusHistory(Order $order): void
    {
        $orderStatusHistory = new OrderStatusHistory([
            'status' => $order->status,
            'comment' => $order->comment,
            'id_author' => auth()->user()->id,
        ]);
        $orderStatusHistory->order()->associate($order);
        $orderStatusHistory->save();  
    }
}
