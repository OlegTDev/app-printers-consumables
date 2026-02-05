<?php
namespace App\Models\Order;

enum OrderStatusEnum: string
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';
    public const STATUS_AGREED = 'agreed';

    public const STATUS_ORDERED = 'ordered';
    public const STATUS_RECEIVED = 'received';

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public static function default(): string
    {
        return self::STATUS_PENDING;
    }

}
