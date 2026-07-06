<?php
declare(strict_types=1);

namespace App\Services\Query;

use App\Models\Order\Order;
use App\Models\Order\OrderStatusEnum;
use App\Models\Order\SubOrderContract;
use Illuminate\Support\Facades\DB;

class OrderQueryService
{
    public function createWithChildOrder(
        SubOrderContract $subOrder,
        string $authUserOrgCode,
        int $authUserId,
        ?string $comment,
        ?string $serviceRequestNumber = null,
        ?string $serviceRequestDate = null,
        int $quantity = 1,
    ): void
    {
        DB::transaction(function () use ($subOrder, $comment, $serviceRequestNumber, $serviceRequestDate, $quantity, $authUserOrgCode, $authUserId) {
            $order = Order::create([
                'org_code' => $authUserOrgCode,
                'status' => OrderStatusEnum::default(),
                'comment' => $comment,
                'requested_by' => $authUserId,
                'service_request_number' => $serviceRequestNumber,
                'service_request_date' => $serviceRequestDate,
                'quantity' => $quantity,
            ]);

            $subOrder->order()->associate($order);
            $subOrder->save();
        });
    }
}
