<?php

namespace Tests\Unit\Models\Auth;

use App\Models\Auth\Ldap;
use App\Models\Auth\LdapFinder;
use LdapRecord\Models\ActiveDirectory\User as LdapUserModel;
use Tests\TestCase;

class LdapTest extends TestCase
{
    public function test_it_returns_formatted_attributes_when_user_is_found(): void
    {
        $mockLdapUser = $this->createMock(LdapUserModel::class);

        $mockLdapUser->method('getFirstAttribute')
            ->willReturnCallback(static fn ($attribute) =>
                match ($attribute) {
                    'username' => 'ivanov',
                    'cn' => 'Иванов Иван',
                    'mail' => 'ivanov@test.com',
                    default => 'some_' . $attribute,
                }
            );

        $mockLdapUser->method('getAttributeValue')
            ->with('memberOf')
            ->willReturn(['CN=Users,DC=local', 'CN=Admins,DC=local']);

        $mockFinder = $this->createMock(LdapFinder::class);
        $mockFinder->expects($this->once())
            ->method('query')
            ->with('ivanov')
            ->willReturn($mockLdapUser);

        $ldapService = new Ldap($mockFinder);

        $result = $ldapService->find('ivanov');

        $this->assertIsArray($result);

        $this->assertEquals('ivanov', $result['username']);
        $this->assertEquals('Иванов Иван', $result['cn']);
        $this->assertEquals('ivanov@test.com', $result['mail']);
        $this->assertEquals('some_department', $result['department']);


        $this->assertIsArray($result['memberOf']);
        $this->assertCount(2, $result['memberOf']);
        $this->assertContains('CN=Admins,DC=local', $result['memberOf']);
    }

    public function test_it_returns_empty_array_if_user_is_not_found(): void
    {
        $mockFinder = $this->createMock(LdapFinder::class);
        $mockFinder->expects($this->once())
            ->method('query')
            ->with('unknown_user')
            ->willReturn(null);

        $ldapService = new Ldap($mockFinder);

        $result = $ldapService->find('unknown_user');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

}
