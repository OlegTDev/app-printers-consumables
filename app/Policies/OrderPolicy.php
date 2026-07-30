<?php

namespace App\Policies;

use App\Models\Order\Order;
use App\Models\Auth\User;
use App\Models\Order\OrderStatusEnum;

class OrderPolicy
{
    public function cancel(User $user, Order $order): bool
    {
        return $user->hasRole('admin') || $user->id === $order->requested_by;
    }

    public function delete(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->id === $order->requested_by
            && $order->status !== OrderStatusEnum::STATUS_COMPLETED->value;
    }

    public function update(User $user, Order $order): bool
    {
        // редактирование документа не возможно, так как заказ уже прошел процедуру согласования
        return $user->hasRole('admin') || (
            $order->requested_by == $user->id
            && \in_array($order->status, [OrderStatusEnum::STATUS_PENDING->value, OrderStatusEnum::STATUS_ORDERED->value]));
    }

}
