<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order\OrderConsumableDetails;
use App\Services\Order\OrderConsumableCountAddedService;

class UpdateConsumableCount
{
    public function __construct(
        private OrderConsumableCountAddedService $orderConsumableCountAddedService,
    )
    {}

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;
        $orderConsumable = OrderConsumableDetails::where('id_order', $order->id)->first();
        if ($orderConsumable) {
            $orgCode = auth()->user()->org_code;
            $idAuthor = auth()->user()->id;
            $this->orderConsumableCountAddedService->pushCount($orderConsumable->id_consumable, $orgCode, $order->quantity, $idAuthor);
        }
    }
}
