<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order\OrderConsumableDetails;
use App\Services\Consumables\ConsumableCountService;

class UpdateConsumableCount
{
    public function __construct(
        private ConsumableCountService $consumableCountService,
    )
    {}

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;
        $orderConsumable = OrderConsumableDetails::where('id_order', $order->id)->first();
        if ($orderConsumable) {
            $idAuthor = auth()->user()->id;
            $this->consumableCountService->add(
                idConsumable: $orderConsumable->id_consumable,
                count: $order->quantity,
                idUser: $idAuthor,
                findOrgCode: $order->org_code,
            );
        }
    }
}
