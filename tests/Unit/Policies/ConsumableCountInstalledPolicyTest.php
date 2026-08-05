<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Policies\ConsumableCountInstalledPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableCountInstalledPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ConsumableCountInstalledPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(ConsumableCountInstalledPolicy::class);
    }

    public function test_can_store_with_has_admin(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->store($admin),
            'Пользователь с ролью "admin" должен иметь право добавлять запись',
        );
    }

    public function test_can_store_with_has_subtract_consumable_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'subtract-consumable']);
        $user->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->store($user),
            'Пользователь с ролью "subtract-consumable" должен иметь право добавлять запись',
        );
    }

    public function test_cant_store_regular_user(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(
            $this->policy->store($user),
            'Простой пользователь не должен иметь право добавлять запись',
        );
    }

    public function test_can_delete_as_admin(): void
    {
        $admin = User::factory()->create();

        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->delete($admin, ConsumableCountInstalled::factory()->create()),
            'Пользователь с ролью "admin" должен иметь право удалять любую запись',
        );
    }

    public function test_can_delete_as_author(): void
    {
        $author = User::factory()->create();
        $consumableCountInstalled = ConsumableCountInstalled::factory()->for($author, 'author')->create();

        $this->assertTrue(
            $this->policy->delete($author, $consumableCountInstalled),
            'Автор должен иметь право удалять свою запись',
        );
    }

    public function test_cant_delete(): void
    {
        $consumableCountInstalled = ConsumableCountInstalled::factory()->create();
        $regularUser = User::factory()->create();

        $this->assertFalse(
            $this->policy->delete($regularUser, $consumableCountInstalled),
            'Обычный пользователь не должен удалять чужую запись',
        );
    }

}
