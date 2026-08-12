<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Order\Order;
use App\Models\Order\OrderStatusEnum;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(OrderPolicy::class);
    }

    public function test_can_cancel_as_admin(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->cancel($admin, Order::withoutEvents(static fn() => Order::factory()->create())),
            'Пользователь с ролью "admin" должен иметь право отменять запись',
        );
    }

    public function test_can_cancel_as_author(): void
    {
        $user = User::factory()->create();
        $order = Order::withoutEvents(static fn () => Order::factory()->for($user, 'requested')->create());

        $this->assertTrue(
            $this->policy->cancel($user, $order),
            'Автор должен иметь право отменять запись',
        );
    }

    public function test_cant_cancel_as_regular_user(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(
            $this->policy->cancel($user, Order::withoutEvents(static fn() => Order::factory()->create())),
            'Простой пользователь не должен иметь право отменять запись',
        );
    }


    public function test_can_delete_as_admin(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->delete($admin, Order::withoutEvents(static fn() => Order::factory()->create())),
            'Пользователь с ролью "admin" должен иметь право удалить запись',
        );
    }

    public function test_can_delete_as_author(): void
    {
        $author = User::factory()->create();
        $order = Order::withoutEvents(static fn () =>
            Order::factory()
                ->for($author, 'requested')
                ->create(['status' => OrderStatusEnum::STATUS_PENDING->value])
        );

        $this->assertTrue(
            $this->policy->delete($author, $order),
            'Автор должен иметь право удалять запись',
        );
    }

    public function test_cant_delete_as_author(): void
    {
        $author = User::factory()->create();
        $order = Order::withoutEvents(static fn () =>
            Order::factory()
                ->for($author, 'requested')
                ->create(['status' => OrderStatusEnum::STATUS_COMPLETED->value])
        );

        $this->assertFalse(
            $this->policy->delete($author, $order),
            'Автор не должен иметь право удалять запись, если статус ' . OrderStatusEnum::STATUS_COMPLETED->value,
        );
    }

    public function test_cant_delete_as_regular_user(): void
    {
        $regularUser = User::factory()->create();

        $this->assertFalse(
            $this->policy->delete($regularUser, Order::withoutEvents(static fn() => Order::factory()->create())),
            'Простой не должен иметь право удалять запись',
        );
    }



    public function test_can_update_as_admin(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->update($admin, Order::withoutEvents(static fn() => Order::factory()->create())),
            'Пользователь с ролью "admin" должен иметь право изменить запись',
        );
    }

    public function test_can_update_as_author(): void
    {
        $author = User::factory()->create();
        $order = Order::withoutEvents(static fn () =>
            Order::factory()
                ->for($author, 'requested')
                ->create(['status' => OrderStatusEnum::default()])
        );

        $this->assertTrue(
            $this->policy->update($author, $order),
            'Автор должен иметь право изменять запись',
        );
    }

    public function test_cant_update_as_regular_user(): void
    {
        $regularUser = User::factory()->create();

        $this->assertFalse(
            $this->policy->update($regularUser, Order::withoutEvents(static fn() => Order::factory()->create())),
            'Простой не должен иметь право изменять запись',
        );
    }

}
