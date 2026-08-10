<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Order\OrderConsumableDetails;
use App\Models\Order\OrderStatusEnum;
use App\Models\Organization;
use App\Services\Query\OrderQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderQueryService::class);
    }


    public function test_it_create_order_consumable(): void
    {
        $org = Organization::factory()->create();
        $author = User::factory()->create(['org_code' => $org->code]);
        Auth::login($author);
        $consumable = Consumable::factory()->create(['id_author' => $author->id]);

        $subOrder = new OrderConsumableDetails([
            'id_consumable' => $consumable->id,
            'id_author' => $author->id,
            'quantity' => 0,
        ]);

        $this->service->createWithChildOrder(
            subOrder: $subOrder,
            comment: 'comment test',
            serviceRequestNumber: '123',
            serviceRequestDate: now()->subDays(1)->toDateString(),
            quantity: 5,
            authUserOrgCode: $org->code,
            authUserId: $author->id,
        );

        $this->assertTrue($subOrder->exists());
        $this->assertTrue($subOrder->order->exists());
        $this->assertEquals(5, $subOrder->order->quantity);
        $this->assertEquals('comment test', $subOrder->order->comment);
        $this->assertEquals('123', $subOrder->order->service_request_number);
        $this->assertEquals(now()->subDays(1)->toDateString(), $subOrder->order->service_request_date);
        $this->assertEquals(OrderStatusEnum::default(), $subOrder->order->status);
    }


}
