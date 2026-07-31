<?php

namespace Tests\Unit\Models\Auth;

use App\Models\Auth\User;
use App\Models\Auth\UserProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProvisionerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_create_user(): void
    {
        $fakeUser = [
            'userPrincipalName' => '0000-00-0001@test.com',
            'company' => 'Some company',
            'cn' => 'Иванов Иван Иванович',
            'department' => 'Отдел ИТ',
            'title' => 'Специалист',
            'telephoneNumber' => '8(888)8888888',
            'mail' => 'admin@test.com',
            'memberOf' => [],
        ];
        $username = '0000-00-0001';

        // create

        $this->assertNull(User::query()->where('name', $username)->first());

        $userProvisioner = $this->app->make(UserProvisioner::class);
        $userResult = $userProvisioner->get($username, 'test', $fakeUser);

        $userModel = User::query()->where('name', $username)->firstOrFail();

        $this->assertTrue($userModel->is($userResult));


        // update

        $fakeUser['company'] = $company = 'New company';
        $fakeUser['cn'] = $fio = 'Петров Иван Иванович';
        $fakeUser['department'] = $department = 'Тех отдел';
        $fakeUser['title'] = $title = 'Ведущий специалист';
        $fakeUser['telephoneNumber'] = $telephone = '8(999)9999999';

        $userResult = $userProvisioner->get($username, 'test', $fakeUser);

        $userModel->refresh();

        $this->assertTrue($userModel->is($userResult));
        $this->assertEquals($company, $userModel->company);
        $this->assertEquals($fio, $userModel->fio);
        $this->assertEquals($department, $userModel->department);
        $this->assertEquals($title, $userModel->post);
        $this->assertEquals($telephone, $userModel->telephone);

    }

}
