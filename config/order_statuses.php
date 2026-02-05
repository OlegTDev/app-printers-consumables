<?php

use App\Models\Order\OrderStatusEnum;
use App\Models\Order\Roles;

/**
 * @var array<string, array{
 *     label: string,
 *     next: list<string>,
 *     roles: list<string>,
 *     color: string
 * }>
 */
return [
    OrderStatusEnum::STATUS_PENDING => [
        'label' => 'на согласовании',
        'next' => [
            OrderStatusEnum::STATUS_AGREED,
            OrderStatusEnum::STATUS_REJECTED,
            OrderStatusEnum::STATUS_CANCELLED,
        ],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value, Roles::ORDER_AUTHOR->value],
        'color' => 'warning',
    ],
    OrderStatusEnum::STATUS_REJECTED => [
        'label' => 'отказан',
        'next' => [],
        'roles' => [Roles::ORDER_APPROVER->value],
        'color' => 'danger',
    ],
    OrderStatusEnum::STATUS_AGREED => [
        'label' => 'согласован',
        'next' => [
            OrderStatusEnum::STATUS_ORDERED,
            OrderStatusEnum::STATUS_CANCELLED,
        ],
        'roles' => [Roles::ORDER_APPROVER->value],
        'color' => 'info',
    ],
    OrderStatusEnum::STATUS_ORDERED => [
        'label' => 'заказан',
        'next' => [
            OrderStatusEnum::STATUS_RECEIVED,
            OrderStatusEnum::STATUS_CANCELLED,
        ],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value],
        'color' => 'info',
    ],
    OrderStatusEnum::STATUS_RECEIVED => [
        'label' => 'получен',
        'next' => [
            OrderStatusEnum::STATUS_COMPLETED,
            OrderStatusEnum::STATUS_CANCELLED,
        ],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value],
        'color' => 'info',
    ],
    OrderStatusEnum::STATUS_COMPLETED => [
        'label' => 'завершен',
        'next' => [],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value],
        'color' => 'success',
    ],
    OrderStatusEnum::STATUS_CANCELLED => [
        'label' => 'отменен',
        'next' => [],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value, Roles::ORDER_AUTHOR->value],
        'color' => 'danger',
    ],
];
