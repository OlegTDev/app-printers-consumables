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
    OrderStatusEnum::STATUS_PENDING->value => [
        'label' => 'на согласовании',
        'next' => [
            OrderStatusEnum::STATUS_AGREED->value,
            OrderStatusEnum::STATUS_REJECTED->value,
            OrderStatusEnum::STATUS_CANCELLED->value,
        ],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value, Roles::ORDER_AUTHOR->value],
        'color' => 'warning',
    ],
    OrderStatusEnum::STATUS_REJECTED->value => [
        'label' => 'отказан',
        'next' => [],
        'roles' => [Roles::ORDER_APPROVER->value],
        'color' => 'danger',
    ],
    OrderStatusEnum::STATUS_AGREED->value => [
        'label' => 'согласован',
        'next' => [
            OrderStatusEnum::STATUS_ORDERED->value,
            OrderStatusEnum::STATUS_CANCELLED->value,
        ],
        'roles' => [Roles::ORDER_APPROVER->value],
        'color' => 'info',
    ],
    OrderStatusEnum::STATUS_ORDERED->value => [
        'label' => 'заказан',
        'next' => [
            OrderStatusEnum::STATUS_RECEIVED->value,
            OrderStatusEnum::STATUS_CANCELLED->value,
        ],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value],
        'color' => 'info',
    ],
    OrderStatusEnum::STATUS_RECEIVED->value => [
        'label' => 'получен',
        'next' => [
            OrderStatusEnum::STATUS_COMPLETED->value,
            OrderStatusEnum::STATUS_CANCELLED->value,
        ],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value, Roles::ORDER_AUTHOR->value],
        'color' => 'info',
    ],
    OrderStatusEnum::STATUS_COMPLETED->value => [
        'label' => 'завершен',
        'next' => [],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value],
        'color' => 'success',
    ],
    OrderStatusEnum::STATUS_CANCELLED->value => [
        'label' => 'отменен',
        'next' => [],
        'roles' => [Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value, Roles::ORDER_AUTHOR->value],
        'color' => 'danger',
    ],
];
