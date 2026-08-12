<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusEnum;
use App\Models\Order\OrderStatusHistory;
use App\Models\Organization;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderTest extends TestCase
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

    private function createOrder(): Order
    {
        return Order::factory()
            ->for($this->organization)
            ->for($this->adminUser, 'requested')
            ->create(['status' => OrderStatusEnum::default()]);
    }

    public function test_it_belongs_to_parent_requested(): void
    {
        $order = $this->createOrder();
        $this->assertTrue($this->adminUser->is($order->requested));
    }

    public function test_it_automatically_sets_requested_by_from_auth(): void
    {
        $order = Order::factory()->for($this->organization)->create(['status' => OrderStatusEnum::default()]);
        $this->assertEquals($this->adminUser->id, $order->requested_by);
    }

    public function test_it_automatically_sets_status_when_created(): void
    {
        $order = $this->createOrder();
        $this->assertCount(1, $order->statusHistory);
        $this->assertEquals($order->status, $order->statusHistory->first()->status);
        $this->assertEquals($order->comment, $order->statusHistory->first()->comment);
        $this->assertEquals($order->requested_by, $order->statusHistory->first()->id_author);
    }

    public function test_it_has_many_history_statuses(): void
    {
        $order = Order::withoutEvents(fn() => $this->createOrder());
        $this->assertCount(0, $order->statusHistory);

        $generateStatusHistory = static fn(User $user, Order $order, OrderStatusEnum $status, CarbonInterface $date): OrderStatusHistory =>
            OrderStatusHistory::factory()
                ->for($order)
                ->for($user, 'author')
                ->create([
                    'status' => $status,
                    'comment' => fake()->realText(50),
                    'created_at' => $date,
                ]);
        $orderStatusHistory1 = $generateStatusHistory($this->adminUser, $order, OrderStatusEnum::STATUS_AGREED, now()->subDays(5));
        $orderStatusHistory2 = $generateStatusHistory($this->adminUser, $order, OrderStatusEnum::STATUS_RECEIVED, now()->subDays(2));
        $order->refresh();

        $this->assertCount(2, $order->statusHistory);
        $this->assertEqualsCanonicalizing([$orderStatusHistory1->id, $orderStatusHistory2->id], $order->statusHistory->pluck('id')->all());
    }

    public function test_it_belongs_to_parent_organization(): void
    {
        $order = $this->createOrder();
        $this->assertTrue($this->organization->is($order->organization));
    }

    public function test_it_sets_status(): void
    {
        $order = Order::withoutEvents(fn() => $this->createOrder());
        $this->assertEquals(OrderStatusEnum::default(), $order->status);

        Order::withoutEvents(static fn () => $order->setStatus(OrderStatusEnum::STATUS_COMPLETED->value));
        $order->refresh();
        $this->assertEquals(OrderStatusEnum::STATUS_COMPLETED->value, $order->status);
    }

    public function test_it_automatically_create_status_history_on_update(): void
    {
        /** @var Order */
        $order = Order::withoutEvents(fn() => $this->createOrder());

        $status1 = OrderStatusEnum::STATUS_ORDERED->value;
        $order->setStatus($status1);
        $order->refresh();
        $this->assertCount(1, $order->statusHistory);

        $status2 = OrderStatusEnum::STATUS_COMPLETED->value;
        $order->setStatus($status2);
        $order->refresh();
        $this->assertCount(2, $order->statusHistory);

        $this->assertEqualsCanonicalizing([$status1, $status2], $order->statusHistory->pluck('status')->all());
    }
}
