<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCountAdded;
use App\Policies\ConsumableCountAddedPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableCountAddedPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ConsumableCountAddedPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(ConsumableCountAddedPolicy::class);
    }

    public function test_can_delete_as_admin(): void
    {
        $admin = User::factory()->create();

        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->delete($admin, ConsumableCountAdded::factory()->create()),
            'Админ должен иметь право удалять любую запись',
        );
    }

    public function test_can_delete_as_author(): void
    {
        $author = User::factory()->create();
        $consumableCountAdded = ConsumableCountAdded::factory()->for($author, 'author')->create();

        $this->assertTrue(
            $this->policy->delete($author, $consumableCountAdded),
            'Автор должен иметь право удалять свою запись',
        );
    }

    public function test_cant_delete(): void
    {
        $consumableCountAdded = ConsumableCountAdded::factory()->create();
        $regularUser = User::factory()->create();

        $this->assertFalse(
            $this->policy->delete($regularUser, $consumableCountAdded),
            'Обычный пользователь не должен удалять чужую запись',
        );
    }

}
