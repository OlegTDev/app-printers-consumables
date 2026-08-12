<?php

namespace Tests\Unit\Services;

use App\Models\Auth\User;
use App\Models\Organization;
use App\Services\Access\OrganizationAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrganizationAccessService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrganizationAccessService::class);
    }


    public function test_it_available_organizations_by_admin(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002']]);
        $admin = User::factory()->withRoleAdmin()->create(['org_code' => '001']);

        $this->assertTrue($this->service->isAvailableByOrgCode('001', true, $admin->id));
        $this->assertFalse($this->service->isAvailableByOrgCode('003', true, $admin->id));
    }

    public function test_it_available_organizations_by_user(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002'], ['code' => '003']]);
        /** @var User */
        $user = User::factory()->create(['org_code' => '001']);
        $user->organizations()->attach(['001', '002']);

        $this->assertTrue($this->service->isAvailableByOrgCode('001', false, $user->id));
        $this->assertTrue($this->service->isAvailableByOrgCode('002', false, $user->id));
        $this->assertFalse($this->service->isAvailableByOrgCode('003', false, $user->id));
        $this->assertFalse($this->service->isAvailableByOrgCode('004', false, $user->id));
    }

    public function test_it_unavailable_organizations(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002']]);
        /** @var User */
        $user = User::factory()->create(['org_code' => '001']);

        $this->assertFalse($this->service->isAvailableByOrgCode('001', false, $user->id));
        $this->assertFalse($this->service->isAvailableByOrgCode('002', false, $user->id));
        $this->assertFalse($this->service->isAvailableByOrgCode('003', false, $user->id));
    }

    public function test_it_get_available_first_code_organization_by_user(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002'], ['code' => '003']]);
        /** @var User */
        $user = User::factory()->create(['org_code' => '001']);
        $user->organizations()->attach(['002', '001']);

        $firstCode = $this->service->getUserAvailableFirstCode(false, $user->id);
        $this->assertContains($firstCode, ['001', '002']);
    }

    public function test_it_get_available_first_code_organization_by_admin(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002']]);
        $admin = User::factory()->withRoleAdmin()->create(['org_code' => '001']);

        $firstCode = $this->service->getUserAvailableFirstCode(true, $admin->id);
        $this->assertContains($firstCode, ['001', '002']);
    }

    public function test_it_get_unavailable_first_code_organization_by_regular_user(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002']]);
        $user = User::factory()->create(['org_code' => '001']);

        $firstCode = $this->service->getUserAvailableFirstCode(false, $user->id);
        $this->assertNotContains($firstCode, ['001', '002']);
    }
}
