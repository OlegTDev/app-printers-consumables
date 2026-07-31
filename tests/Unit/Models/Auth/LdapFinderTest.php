<?php

namespace Tests\Unit\Models\Auth;

use App\Models\Auth\LdapFinder;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Testing\DirectoryFake;
use LdapRecord\Testing\LdapFake;
use Tests\TestCase;

class LdapFinderTest extends TestCase
{

    public function test_it_can_find_user_by_samaccountname(): void
    {
        $fakeLdapRecord = [
            'dn' => 'cn=John Doe,dc=local,dc=com',
            'cn' => ['John Doe'],
            'samaccountname' => ['johndoe'],
            'mail' => ['johndoe@company.com'],
        ];

        /** @var LdapFake $connection */
        $connection = DirectoryFake::setup()
            ->getLdapConnection();

        $connection
            ->expect(
                (new \LdapRecord\Testing\LdapExpectation('search'))
                    ->once()
                    ->andReturn([$fakeLdapRecord])
            );


        $finder = $this->app->make(LdapFinder::class);

        $result = $finder->query('johndoe');

        $this->assertNotNull($result);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('johndoe', $result->getFirstAttribute('samaccountname'));
    }

}
