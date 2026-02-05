<?php
namespace App\Models\Order;

enum Roles: string
{
    case ORDER_APPROVER = 'order-approver';
    case ORDER_EXECUTOR = 'order-executor';
    case ORDER_AUTHOR = 'order-author';
}
