<?php

namespace Tests\Unit\Models\Auth;

use App\Models\Auth\Ldap;
use App\Models\Auth\LdapFinder;
use App\Models\Auth\LdapUser;
use App\Models\Auth\User;
use App\Models\Auth\UserProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LdapRecord\Models\ActiveDirectory\User as LdapUserModel;
use Tests\TestCase;

class LdapUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_find_a_user_by_username(): void
    {
        $ldapAdmin = (new LdapUserModel())
            ->setRawAttributes([
                'dn' => 'cn=admin,dc=local,dc=com',
                'cn' => ['Administrator'],
                'samaccountname' => ['admin'],
                'mail' => ['admin@test.com'],
                'userPrincipalName' => ['admin@test.com'],
            ]);

        $ldapUser = (new LdapUserModel())
            ->setRawAttributes([
                'dn' => 'cn=user,dc=local,dc=com',
                'cn' => ['User'],
                'samaccountname' => ['user'],
                'mail' => ['user@test.com'],
                'userPrincipalName' => ['user@test.com'],
            ]);

        $mockFinder = $this->createMock(LdapFinder::class);
        $mockFinder->method('query')
            ->willReturnCallback(static fn(string $username) =>
                match($username) {
                    'admin' => $ldapAdmin,
                    'user' => $ldapUser,
                    default => null,
                });

        $ldapService = new Ldap($mockFinder);
        $userProvisionerService = $this->app->make(UserProvisioner::class);

        $ldapUserService = new LdapUser($ldapService, $userProvisionerService);

        $this->assertNull($ldapUserService->findOrCreate('user-not-found', 'test'));

        $user = $ldapUserService->findOrCreate('admin', 'test');
        $userModel = User::query()->where('name', 'admin')->first();

        $this->assertTrue($userModel->is($user));
    }

}
