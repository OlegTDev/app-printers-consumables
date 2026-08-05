<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCount;
use App\Models\Organization;
use App\Policies\ConsumableCountPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableCountPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ConsumableCountPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(ConsumableCountPolicy::class);
    }

    public function test_can_show_as_admin(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->show($admin, ConsumableCount::factory()->create()),
            'Пользователь с ролью "admin" должен иметь право просматривать запись',
        );
    }

    public function test_can_show_as_user_allow(): void
    {
        Organization::factory()->count(5)->create();
        $organization = Organization::inRandomOrder()->firstOrFail();
        $user = User::factory()->create(['org_code' => $organization]);
        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->create();
        $consumableCount->organizations()->attach([$organization->code]);

        $this->assertTrue(
            $this->policy->show($user, $consumableCount),
            'Пользователю с установленной организацией, такой же как в остатке должен иметь право просматривать запись',
        );
    }

    public function test_cant_show_as_user(): void
    {
        Organization::factory()->count(2)->create();
        $organizations = Organization::get();
        $regularUser = User::factory()->create(['org_code' => $organizations[0]]);

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->create();
        $consumableCount->organizations()->attach([$organizations[1]->code]);

        $this->assertFalse(
            $this->policy->show($regularUser, $consumableCount),
            'Пользователь, у которого текущая организация не входит в перечень организаций в остатке не должен просматривать запись',
        );
    }



}
