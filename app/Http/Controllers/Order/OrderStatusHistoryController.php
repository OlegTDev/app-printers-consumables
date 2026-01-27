<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderStatusHistoryResource;
use App\Models\Order\Order;

class OrderStatusHistoryController extends Controller
{
    /**
     * @route GET /orders/{order}/status-history
     */
    public function index(Order $order)
    {
        return [
            'statuses' => OrderStatusHistoryResource::collection($order->statusHistory()->orderBy('created_at')->get()),
            'labels' => config('labels.order_status_history'),
        ];
    }
}
