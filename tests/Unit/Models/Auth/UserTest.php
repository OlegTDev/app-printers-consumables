<?php

namespace Tests\Unit\Models\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_scope_filter_by_name(): void
    {
        User::factory()
            ->count(3)
            ->sequence(
                ['name' => 'admin_test'],
                ['name' => 'user_test_1'],
                ['name' => 'user_test_2'],
            )
            ->create();

        $filteredUsersName = User::query()->filter(['search' => 'user_test'])->get()->pluck('name')->toArray();
        $this->assertCount(2, $filteredUsersName);
        $this->assertContains('user_test_1', $filteredUsersName);
        $this->assertContains('user_test_2', $filteredUsersName);
        $this->assertNotContains('admin_test', $filteredUsersName);
    }

    public function test_it_scope_filter_by_email(): void
    {
        User::factory()
            ->count(2)
            ->sequence(
                ['email' => 'admin_test@test.com'],
                ['email' => 'user_test_1@test.com'],
            )
            ->create();

        $searchEmail = 'user_test_1@test.com';
        $filteredUsersEmail = User::query()->filter(['search' => $searchEmail])->get();
        $this->assertCount(1, $filteredUsersEmail);
        $this->assertEquals($searchEmail, $filteredUsersEmail->first()->email);
    }

    public function test_it_scope_filter_by_fio(): void
    {
        $users = User::factory()
            ->count(3)
            ->sequence(
                ['name' => 'user1', 'fio' => 'Иванов Иван Петрович'],
                ['name' => 'user2', 'fio' => 'Петров Петр Иванович'],
                ['name' => 'user3', 'fio' => 'Пользователь'],
            )
            ->create()
            ->keyBy('name')
            ->all();

        $filteredUsersFio = User::query()->filter(['search' => 'иван'])->get();
        $this->assertCount(2, $filteredUsersFio);
        $this->assertEquals('Иванов Иван Петрович', $users['user1']->fio);
        $this->assertEquals('Петров Петр Иванович', $users['user2']->fio);
    }

    public function test_it_scope_filter_by_roles(): void
    {
        /** @var User[] */
        $users = User::factory()
            ->count(3)
            ->sequence(
                ['name' => 'admin'],
                ['name' => 'user1'],
                ['name' => 'user2'],
            )
            ->create()
            ->keyBy('name')
            ->all();

        /** @var Role[] */
        $roles = Role::factory()
            ->createMany([['name' => 'admin'], ['name' => 'some-role']])
            ->keyBy('name')
            ->all();

        $users['admin']->roles()->attach([$roles['admin']->id]);
        $users['user1']->roles()->attach([$roles['some-role']->id]);
        $users['user2']->roles()->attach([$roles['some-role']->id]);

        $filteredUsersByRoleAdmin = User::query()->filter(['roles' => ['admin']])->get();
        $this->assertCount(1, $filteredUsersByRoleAdmin);
        $this->assertTrue($users['admin']->is($filteredUsersByRoleAdmin->first()));

        $filteredUsersByRoleSomeRole = User::query()->filter(['roles' => ['some-role']])->get();
        $this->assertCount(2, $filteredUsersByRoleSomeRole);
        $this->assertNull($filteredUsersByRoleSomeRole->where('name', 'admin')->first());
    }

    public function test_it_scope_filter_by_role_and_name(): void
    {
        /** @var User[] */
        $users = User::factory()
            ->count(3)
            ->sequence(
                ['name' => 'admin'],
                ['name' => 'user1'],
                ['name' => 'user2'],
            )
            ->create()
            ->keyBy('name')
            ->all();

        /** @var Role[] */
        $roles = Role::factory()
            ->createMany([['name' => 'some-role']])
            ->keyBy('name')
            ->all();

        $users['user1']->roles()->attach([$roles['some-role']->id]);
        $users['user2']->roles()->attach([$roles['some-role']->id]);

        $filteredUsersByRoleSomeRoleAndName = User::query()->filter(['roles' => ['some-role'], 'search' => 'user2'])->get();
        $this->assertCount(1, $filteredUsersByRoleSomeRoleAndName);
        $this->assertNull($filteredUsersByRoleSomeRoleAndName->where('name', 'user1')->first());
        $this->assertTrue($users['user2']->is($filteredUsersByRoleSomeRoleAndName->first()));
    }

    public function test_it_available_organizations(): void
    {
        /** @var Organization[] */
        $organizations = Organization::factory()
            ->createMany([['code' => '001'], ['code' => '002'], ['code' => '003']])
            ->keyBy('code')
            ->all();

        $admin = User::factory()->withRoleAdmin()->create(['org_code' => '001']);

        /** @var User */
        $userWithOrganizations = User::factory()->create(['org_code' => '001']);
        $userWithOrganizations->organizations()->attach(['001', '002']);

        /** @var User */
        $user = User::factory()->create(['org_code' => '001']);

        $this->assertCount(\count($organizations), $admin->availableOrganizations());
        $userAvailableOrganizationsCodes = collect($userWithOrganizations->availableOrganizations())->pluck('code')->toArray();
        $this->assertEqualsCanonicalizing(['001', '002'], $userAvailableOrganizationsCodes);

        $this->assertEmpty($user->availableOrganizations());
    }

    public function test_it_update_roles(): void
    {
        /** @var User */
        $admin = User::factory()->withRoleAdmin()->create();
        /** @var Role */
        $role = Role::factory()->create();

        $this->assertEqualsCanonicalizing(['admin'], $admin->roles->pluck('name')->all());

        $admin->updateRoles([$role->name]);
        $admin->refresh();

        $this->assertEqualsCanonicalizing([$role->name], $admin->roles->pluck('name')->all());
    }

    public function test_it_is_admin(): void
    {
        $userAdmin = User::factory()->withRoleAdmin()->create();
        $userNotAdmin = User::factory()->create();

        $this->assertTrue($userAdmin->isAdmin());
        $this->assertFalse($userNotAdmin->isAdmin());
    }

    public function test_it_has_organization(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002'], ['code' => '003']]);
        /** @var User[] */
        $users = User::factory()
            ->createMany([['name' => 'user1'], ['name' => 'user2']])
            ->keyBy('name')
            ->all();

        $users['user1']->organizations()->attach(['001']);
        $users['user2']->organizations()->attach(['001', '002']);

        $this->assertFalse($users['user1']->hasOrganization('002'));
        $this->assertTrue($users['user2']->hasOrganization('001'));
        $this->assertTrue($users['user2']->hasOrganization(['001', '002']));
        $this->assertFalse($users['user1']->hasOrganization('003'));
    }

    public function test_it_update_organization(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002'], ['code' => '003']]);
        /** @var User */
        $user = User::factory()->create();
        $user->organizations()->attach(['001', '002']);
        $this->assertEqualsCanonicalizing(['001', '002'], $user->organizations->pluck('code')->all());

        $user->updateOrganizations(['002', '003']);
        $user->refresh();

        $this->assertEqualsCanonicalizing(['002', '003'], $user->organizations->pluck('code')->all());
    }

}
