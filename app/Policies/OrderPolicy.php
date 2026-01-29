<?php

namespace App\Policies;

use App\Models\Order\Order;
use App\Models\Auth\User;

class OrderPolicy
{

    public function update(User $user, Order $order): bool
    {
        // редактирование документа не возможно, так как заказ уже прошел процедуру согласования
        return $user->can('admin') || (
            $order->requested_by == $user->id
            && in_array($order->status, [Order::STATUS_PENDING]));
    }
    
}
