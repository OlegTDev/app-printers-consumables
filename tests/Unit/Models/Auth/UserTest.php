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


    /** @var User[] */
    private array $users = [];

    /** @var Organization[] */
    private array $organizations = [];

    public function setUp(): void
    {
        parent::setUp();


        $this->users = [
            'admin_test' => User::factory()->create(['name' => 'admin_test', 'email' => 'admin_test@test.com', 'fio' => 'Админ', 'org_code' => '']),
            'user_test_1' => User::factory()->create(['name' => 'user_test_1', 'email' => 'user_test_1@test.com', 'fio' => 'Иванов Иван Петрович', 'org_code' => '']),
            'user_test_2' => User::factory()->create(['name' => 'user_test_2', 'email' => 'user_test_2@test.com', 'fio' => 'Петров Петр Иванович', 'org_code' => '']),
            'user_test_3' => User::factory()->create(['name' => 'user_test_3', 'email' => 'user_test_3@test.com', 'fio' => 'Пользователь', 'org_code' => '']),
        ];

        /** @var Role[] */
        $roles = Role::factory()->createMany([
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'some_role_1', 'description' => 'Some role 1'],
            ['name' => 'some_role_2', 'description' => 'Some role 2'],
        ])->keyBy('name')->all();

        $this->users['admin_test']->roles()->attach([$roles['admin']->id]);
        $this->users['user_test_2']->roles()->attach([$roles['some_role_1']->id]);
        $this->users['user_test_3']->roles()->attach([$roles['some_role_1']->id]);


        $this->organizations = Organization::factory()->createMany([
            ['code' => '0001', 'name' => 'Org 1'],
            ['code' => '0002', 'name' => 'Org 2'],
            ['code' => '0003', 'name' => 'Org 3'],
        ])->keyBy('code')->all();

        $this->users['user_test_2']->organizations()->attach(['0001', '0002']);
    }

    public function test_it_scope_filter_by_name(): void
    {
        $filteredUsersName = User::query()->filter(['search' => 'user_test'])->get()->pluck('name')->toArray();
        $this->assertCount(3, $filteredUsersName);
        $this->assertContains('user_test_1', $filteredUsersName);
        $this->assertContains('user_test_2', $filteredUsersName);
        $this->assertContains('user_test_3', $filteredUsersName);
        $this->assertNotContains('admin_test', $filteredUsersName);
    }

    public function test_it_scope_filter_by_email(): void
    {
        $searchEmail = 'user_test_2@test.com';
        $filteredUsersEmail = User::query()->filter(['search' => $searchEmail])->get();
        $this->assertCount(1, $filteredUsersEmail);
        $this->assertEquals($searchEmail, $filteredUsersEmail->first()->email);
    }

    public function test_it_scope_filter_by_fio(): void
    {
        $filteredUsersFio = User::query()->filter(['search' => 'иван'])->get();
        $this->assertCount(2, $filteredUsersFio);
        $user1 = $filteredUsersFio->where('name', 'user_test_1')->first();
        $this->assertEquals('Иванов Иван Петрович', $user1->fio);
        $user2 = $filteredUsersFio->where('name', 'user_test_2')->first();
        $this->assertEquals('Петров Петр Иванович', $user2->fio);
    }

    public function test_it_scope_filter_by_roles(): void
    {
        $filteredUsersByRoleAdmin = User::query()->filter(['roles' => ['admin']])->get();
        $this->assertCount(1, $filteredUsersByRoleAdmin);
        $this->assertEquals('Админ', $filteredUsersByRoleAdmin->first()->fio);

        $filteredUsersByRoleSomeRole = User::query()->filter(['roles' => ['some_role_1']])->get();
        $this->assertCount(2, $filteredUsersByRoleSomeRole);
        $this->assertNull($filteredUsersByRoleSomeRole->where('name', 'admin')->first());
    }

    public function test_it_scope_filter_by_role_and_name(): void
    {
        $filteredUsersByRoleSomeRoleAndName = User::query()->filter(['roles' => ['some_role_1'], 'search' => 'user_test_2'])->get();
        $this->assertCount(1, $filteredUsersByRoleSomeRoleAndName);
        $this->assertNull($filteredUsersByRoleSomeRoleAndName->where('name', 'user_test_3')->first());
        $this->assertEquals('Петров Петр Иванович', $filteredUsersByRoleSomeRoleAndName->first()->fio);
    }

    public function test_it_available_organizations(): void
    {
        $this->assertCount(\count($this->organizations), $this->users['admin_test']->availableOrganizations());

        $userAvailableOrganizationsCodes = collect($this->users['user_test_2']->availableOrganizations())->pluck('code')->toArray();
        $this->assertEqualsCanonicalizing(['0001', '0002'], $userAvailableOrganizationsCodes);

        $this->assertEmpty($this->users['user_test_1']->availableOrganizations());
    }

    public function test_it_update_roles(): void
    {
        $user = $this->users['user_test_2'];

        $userRoles = $user->roles()->get()->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(['some_role_1'], $userRoles);

        $user->updateRoles(['admin']);
        $user->refresh();

        $userRoles = $user->roles()->get()->pluck('name')->toArray();
        $this->assertEqualsCanonicalizing(['admin'], $userRoles);
    }

    public function test_it_is_admin(): void
    {
        $userAdmin = $this->users['admin_test'];
        $userNotAdmin = $this->users['user_test_2'];

        $this->assertTrue($userAdmin->isAdmin());
        $this->assertFalse($userNotAdmin->isAdmin());
    }

    public function test_it_has_organization(): void
    {
        $userTest1 = $this->users['user_test_1'];
        $userTest2 = $this->users['user_test_2'];

        $this->assertFalse($userTest1->hasOrganization('0001'));

        $this->assertTrue($userTest2->hasOrganization('0001'));
        $this->assertTrue($userTest2->hasOrganization(['0001', '0002']));
        $this->assertFalse($userTest2->hasOrganization('0003'));
    }

    public function test_it_update_organization(): void
    {
        $userTest2 = $this->users['user_test_2'];

        $userOrganizations = $userTest2->organizations()->get()->pluck('code')->toArray();
        $this->assertEqualsCanonicalizing(['0001', '0002'], $userOrganizations);

        $userTest2->updateOrganizations(['0003']);
        $userTest2->refresh();

        $userOrganizations = $userTest2->organizations()->get()->pluck('code')->toArray();
        $this->assertEqualsCanonicalizing(['0003'], $userOrganizations);
    }

}
