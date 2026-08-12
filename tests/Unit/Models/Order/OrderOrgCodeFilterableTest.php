<?php

namespace Tests\Unit\Models\Order;

use App\Models\Auth\Role;
use Auth;
use App\Models\Auth\User;
use App\Models\Order\Order;
use App\Models\Order\OrderMiscDetails;
use App\Models\Order\OrderStatusEnum;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderOrgCodeFilterableTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filtered_by_org_code(): void
    {
        $org1 = Organization::factory()->create(['code' => '01', 'name' => 'Org 1']);
        $org2 = Organization::factory()->create(['code' => '02', 'name' => 'Org 2']);
        $org3 = Organization::factory()->create(['code' => '03', 'name' => 'Org 3']);


        $roleAdmin = Role::factory()->create(['name' => 'admin', 'description' => 'Administrator']);
        $user1 = User::factory()->create(['name' => 'user1', 'email' => 'user1@test.com', 'org_code' => $org1]);
        $user2 = User::factory()->create(['name' => 'user2', 'email' => 'user2@test.com', 'org_code' => $org3]);
        /** @var User */
        $userAdmin = User::factory()->create(['name' => 'admin', 'email' => 'admin@test.com', 'org_code' => $org1]);
        $userAdmin->roles()->attach($roleAdmin);

        $count1 = 10;
        $idsOrg2 = $this->generateOrderMiscDetails($count1, $user1, $org1);
        $count2 = 5;
        $idsOrg3 = $this->generateOrderMiscDetails($count2, $user2, $org3);

        Auth::login($user1);

        $filterAll = OrderMiscDetails::query()->get();
        $this->assertCount($count1 + $count2, $filterAll);

        $filterOrg1 = OrderMiscDetails::query()->filterByOrgCode()->get();
        $this->assertCount($count1, $filterOrg1);

        Auth::login($user2);
        $filterOrg3 = OrderMiscDetails::query()->filterByOrgCode()->get();
        $this->assertCount($count2, $filterOrg3);

        Auth::login($userAdmin);
        $filterWithAdmin = OrderMiscDetails::query()->filterByOrgCode()->get();
        $this->assertCount($count1 + $count2, $filterWithAdmin);
    }

    private function generateOrderMiscDetails(int $count, User $user, Organization $org): array
    {
        $result = [];
        for ($i=0; $i<$count; $i++) {
            $order = Order::withoutEvents(static fn() => Order::factory()
                ->for($org)
                ->for($user, 'requested')
                ->create(['status' => OrderStatusEnum::default()]));
            $orderMiscDetails = OrderMiscDetails::factory()->for($order)->create(['name' => "Misc $i", 'id_author' => $user]);
            $result[] = $orderMiscDetails->id;
        }
        return $result;
    }

}
