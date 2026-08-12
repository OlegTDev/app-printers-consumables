<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Order\Order;
use App\Models\Order\OrderMiscDetails;
use App\Models\Order\OrderStatusEnum;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderMiscDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create(['code' => 'T0001', 'name' => 'Test Org']);
        $this->adminUser = User::factory()->create(['name' => 'admin_test', 'email' => 'admin_test@test.com', 'fio' => 'Админ', 'org_code' => 'T0001']);
        Auth::login($this->adminUser);
    }

    private function createOrder(string $status = null): Order
    {
        return Order::factory()
            ->for($this->organization)
            ->create(['status' => $status ?? OrderStatusEnum::default()]);
    }

    public function test_it_belongs_to_parent_order(): void
    {
        $order = $this->createOrder();
        $orderMiscDetails = OrderMiscDetails::factory()
            ->for($order)
            ->create(['name' => 'Some misc item', 'id_author' => $this->adminUser->id]);

        $this->assertTrue($order->is($orderMiscDetails->order));
    }

    public function test_it_filtered_by_name_or_description_fields(): void
    {
        $order = $this->createOrder();
        /** @var OrderMiscDetails */
        $orderMiscDetails1 = OrderMiscDetails::factory()
            ->for($order)
            ->create(['name' => 'Name misc 1', 'description' => 'Description misc 1', 'id_author' => $this->adminUser->id]);
        /** @var OrderMiscDetails */
        $orderMiscDetails2 = OrderMiscDetails::factory()
            ->for($order)
            ->create(['name' => 'Name misc 2', 'description' => 'Description misc 2', 'id_author' => $this->adminUser->id]);


        $resultFilter1 = OrderMiscDetails::query()->filter(['search' => 'Name misc 1'])->get();
        $this->assertCount(1, $resultFilter1);
        $this->assertEquals($orderMiscDetails1->id, $resultFilter1->first()->id);

        $resultFilter2 = OrderMiscDetails::query()->filter(['search' => 'Description misc 2'])->get();
        $this->assertCount(1, $resultFilter2);
        $this->assertEquals($orderMiscDetails2->id, $resultFilter2->first()->id);
    }

    public function test_it_filtered_by_order_status_field(): void
    {
        $orderPending = $this->createOrder(OrderStatusEnum::STATUS_PENDING->value);
        $orderOrdered = $this->createOrder(OrderStatusEnum::STATUS_ORDERED->value);

        /** @var OrderMiscDetails */
        $orderMiscPending = OrderMiscDetails::factory()
            ->for($orderPending)
            ->create(['name' => 'Some misc 1', 'id_author' => $this->adminUser->id]);

        /** @var OrderMiscDetails */
        $orderMiscOrdered = OrderMiscDetails::factory()
            ->for($orderOrdered)
            ->create(['name' => 'Some misc 2', 'id_author' => $this->adminUser->id]);

        $resultFilterPending = OrderMiscDetails::query()->filter(['status' => OrderStatusEnum::STATUS_PENDING->value])->get();
        $this->assertCount(1, $resultFilterPending);
        $this->assertEquals($orderMiscPending->id, $resultFilterPending->first()->id);

        $resultFilterOrdered = OrderMiscDetails::query()->filter(['status' => OrderStatusEnum::STATUS_ORDERED->value])->get();
        $this->assertCount(1, $resultFilterOrdered);
        $this->assertEquals($orderMiscOrdered->id, $resultFilterOrdered->first()->id);
    }

    public function test_it_filtered_by_organizations_codes(): void
    {
        $organization2 = Organization::factory()->create(['code' => 'T0002', 'name' => 'Org 2']);
        $order1 = $this->createOrder();
        $order2 = Order::factory()->for($organization2)->create(['status' => OrderStatusEnum::STATUS_COMPLETED->value]);

        $orderMiscDetails1 = OrderMiscDetails::factory()
            ->for($order1)
            ->create(['name' => 'Some misc 1', 'id_author' => $this->adminUser->id]);

        $orderMiscDetails2 = OrderMiscDetails::factory()
            ->for($order2)
            ->create(['name' => 'Some misc 2', 'id_author' => $this->adminUser->id]);

        $resultFilter1 = OrderMiscDetails::query()->filter(['organizations' => [$organization2->code]])->get();
        $this->assertCount(1, $resultFilter1);
        $this->assertEquals($orderMiscDetails2->id, $resultFilter1->first()->id);

        $resultFilter2 = OrderMiscDetails::query()->filter(['organizations' => [$this->organization->code, $organization2->code]])->get();
        $this->assertCount(2, $resultFilter2);
        $this->assertEqualsCanonicalizing([$orderMiscDetails1->id, $orderMiscDetails2->id], $resultFilter2->pluck('id')->all());
    }
}
