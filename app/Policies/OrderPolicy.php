<?php

namespace App\Policies;

use App\Models\Order\Order;
use App\Models\Auth\User;
use App\Models\Order\OrderStatusEnum;

class OrderPolicy
{

    public function update(User $user, Order $order): bool
    {
        // редактирование документа не возможно, так как заказ уже прошел процедуру согласования
        return $user->can('admin') || (
            $order->requested_by == $user->id
            && \in_array($order->status, [OrderStatusEnum::STATUS_PENDING, OrderStatusEnum::STATUS_ORDERED]));
    }

}
