<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\User;
use App\Services\Query\UserQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class UserQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserQueryService::class);
    }

    public function test_it_successfully_changes_user_organization(): void
    {
        $user = User::factory()->create(['org_code' => '001']);
        $newOrgCode = '002';

        $result = $this->service->changeUserOrganization($user->id, $newOrgCode);
        $user->refresh();

        $this->assertEquals($newOrgCode, $user->org_code);
    }

    public function test_it_returns_false_and_logs_error_when_user_not_found(): void
    {
        $nonExistentUserId = 999999;
        $newOrgCode = '002';

        Log::expects('error')->once()->with('User not found');

        $result = $this->service->changeUserOrganization($nonExistentUserId, $newOrgCode);
        $this->assertFalse($result);
    }





}
