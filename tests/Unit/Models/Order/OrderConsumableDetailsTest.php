<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderConsumableDetails;
use App\Models\Order\OrderStatusEnum;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderConsumableDetailsTest extends TestCase
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

    private function createOrder(string $status = null, int $quantity = 1): Order
    {
        return Order::factory()
            ->for($this->organization)
            ->create(['status' => $status ?? OrderStatusEnum::default(), 'quantity' => $quantity]);
    }

    private function createConsumable(array $attributes = []): Consumable
    {
        return Consumable::factory()
            ->create([
                'type' => ConsumableTypesEnum::wasteContainer->name,
                'name' => 'Consumable name',
                ...$attributes,
            ]);
    }

    public function test_it_belongs_to_parent_order(): void
    {
        $order = $this->createOrder();
        $consumable = $this->createConsumable();
        $orderConsumableDetails = OrderConsumableDetails::factory()
            ->for($order)
            ->for($consumable)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser->id]);

        $this->assertTrue($order->is($orderConsumableDetails->order));
        $this->assertTrue($consumable->is($orderConsumableDetails->consumable));
    }

    public function test_it_filtered_by_consumable_name_description_fields(): void
    {
        $order = $this->createOrder();
        $consumable1 = $this->createConsumable(['name' => 'Consumable one', 'description' => 'Description one']);
        $consumable2 = $this->createConsumable(['name' => 'Consumable two', 'description' => 'Description two']);
        /** @var OrderConsumableDetails */
        $orderConsumableDetails1 = OrderConsumableDetails::factory()
            ->for($order)
            ->for($consumable1)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser->id]);
        /** @var OrderConsumableDetails */
        $orderConsumableDetails2 = OrderConsumableDetails::factory()
            ->for($order)
            ->for($consumable2)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser->id]);

        $resultFilter1 = OrderConsumableDetails::query()->filter(['search' => 'Consumable one'])->get();
        $this->assertCount(1, $resultFilter1);
        $this->assertEquals($orderConsumableDetails1->id, $resultFilter1->first()->id);

        $resultFilter2 = OrderConsumableDetails::query()->filter(['search' => 'Description two'])->get();
        $this->assertCount(1, $resultFilter2);
        $this->assertEquals($orderConsumableDetails2->id, $resultFilter2->first()->id);
    }

    public function test_it_filtered_by_order_status_field(): void
    {
        $orderPending = $this->createOrder(OrderStatusEnum::STATUS_PENDING->value);
        $orderOrdered = $this->createOrder(OrderStatusEnum::STATUS_ORDERED->value);

        $consumable = $this->createConsumable();

        /** @var OrderConsumableDetails */
        $orderConsumableDetailsPending = OrderConsumableDetails::factory()
            ->for($orderPending)
            ->for($consumable)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser->id]);

        /** @var OrderConsumableDetails */
        $orderConsumableDetailsOrdered = OrderConsumableDetails::factory()
            ->for($orderOrdered)
            ->for($consumable)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser->id]);

        $resultFilterPending = OrderConsumableDetails::query()->filter(['status' => OrderStatusEnum::STATUS_PENDING->value])->get();
        $this->assertCount(1, $resultFilterPending);
        $this->assertEquals($orderConsumableDetailsPending->id, $resultFilterPending->first()->id);

        $resultFilterOrdered = OrderConsumableDetails::query()->filter(['status' => OrderStatusEnum::STATUS_ORDERED->value])->get();
        $this->assertCount(1, $resultFilterOrdered);
        $this->assertEquals($orderConsumableDetailsOrdered->id, $resultFilterOrdered->first()->id);
    }

    public function test_it_filtered_by_organizations_codes(): void
    {
        $organization2 = Organization::factory()->create(['code' => 'T0002', 'name' => 'Org 2']);
        $order1 = $this->createOrder();
        $order2 = Order::factory()->for($organization2)->create(['status' => OrderStatusEnum::STATUS_COMPLETED->value]);

        $consumable = $this->createConsumable();

        $orderConsumableDetails1 = OrderConsumableDetails::factory()
            ->for($order1)
            ->for($consumable)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser->id]);

        $orderConsumableDetails2 = OrderConsumableDetails::factory()
            ->for($order2)
            ->for($consumable)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser->id]);

        $resultFilter1 = OrderConsumableDetails::query()->filter(['organizations' => [$organization2->code]])->get();
        $this->assertCount(1, $resultFilter1);
        $this->assertEquals($orderConsumableDetails2->id, $resultFilter1->first()->id);

        $resultFilter2 = OrderConsumableDetails::query()->filter(['organizations' => [$this->organization->code, $organization2->code]])->get();
        $this->assertCount(2, $resultFilter2);
        $this->assertEqualsCanonicalizing([$orderConsumableDetails1->id, $orderConsumableDetails2->id], $resultFilter2->pluck('id')->all());
    }

    public function test_it_added_consumable_quantity_on_finish_status_order(): void
    {
        Auth::login($this->adminUser);

        $quantity = 20;

        $order = $this->createOrder(quantity: $quantity);
        /** @var Consumable */
        $consumable = Consumable::factory()->create([
            'type' => ConsumableTypesEnum::other->name,
            'name' => 'Some consumable',
            'description' => 'Description consumable',
        ]);

        /** @var OrderConsumableDetails */
        $orderConsumableDetails = OrderConsumableDetails::factory()
            ->for($order)
            ->for($consumable)
            ->create(['quantity' => 1, 'id_author' => $this->adminUser]);

        $order->setStatus(OrderStatusEnum::STATUS_COMPLETED->value);

        $consumablesAdded = $consumable->consumablesCount->first()->consumablesAdded;
        $this->assertCount(1, $consumablesAdded);
        $this->assertEquals($quantity, $consumablesAdded->first()->count);
    }
}
