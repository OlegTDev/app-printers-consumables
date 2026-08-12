<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(UserPolicy::class);
    }

    public function test_can_edit_as_admin(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($role->id);

        $this->assertTrue(
            $this->policy->edit($admin, User::factory()->create()),
            'Пользователь с ролью "admin" должен иметь право изменять запись',
        );
    }

    public function test_can_edit_as_author(): void
    {
        $author = User::factory()->create();

        $this->assertTrue(
            $this->policy->edit($author, $author),
            'Автор должен иметь право изменять запись',
        );
    }

    public function test_cant_cancel_as_regular_user(): void
    {
        $user = User::factory()->create();
        $model = User::factory()->create();

        $this->assertFalse(
            $this->policy->edit($user, $model),
            'Простой пользователь не должен иметь право изменять запись',
        );
    }
}
