<?php
namespace App\Models\Order;

enum OrderStatusEnum: string
{
    case STATUS_PENDING = 'pending';

    case STATUS_REJECTED = 'rejected';
    case STATUS_AGREED = 'agreed';

    case STATUS_ORDERED = 'ordered';
    case STATUS_RECEIVED = 'received';

    case STATUS_COMPLETED = 'completed';
    case STATUS_CANCELLED = 'cancelled';

    public static function default(): string
    {
        return self::STATUS_PENDING->value;
    }

}
