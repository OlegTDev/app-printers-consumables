<?php

namespace Tests\Unit\Policies;

use App\Models\Organization;
use App\Services\Query\OrganizationQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrganizationQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrganizationQueryService::class);
    }

    public function test_it_returns_all_organizations(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002'], ['code' => '003'], ['code' => '004']]);

        $query = $this->service->getAllOrganizationsQuery();
        $result = $query->get();

        $this->assertCount(4, $result);
        $this->assertEqualsCanonicalizing(['001', '002', '003', '004'], $result->pluck('code')->all());
    }

    public function test_it_return_organization_by_codes(): void
    {
        Organization::factory()->createMany([['code' => '001'], ['code' => '002'], ['code' => '003'], ['code' => '004']]);

        $resultEmpty = $this->service->getOrganizationsByCodes([]);
        $this->assertCount(0, $resultEmpty);

        $resultOneElement = $this->service->getOrganizationsByCodes(['002']);
        $this->assertCount(1, $resultOneElement);
        $this->assertEquals('002', $resultOneElement[0]['code']);

        $resultTwoElements = $this->service->getOrganizationsByCodes(['001', '003']);
        $this->assertCount(2, $resultTwoElements);
        $this->assertEqualsCanonicalizing(['001', '003'], [$resultTwoElements[0]['code'],  $resultTwoElements[1]['code']]);
    }

    public function test_it_return_organizations_as_tree(): void
    {
        $items = [
            '001' => ['code' => '001', 'name' => 'ParentOrg 1', 'parent' => null, 'created_at' => null, 'updated_at'=> null],
            '002' => ['code' => '002', 'name' => 'ParentOrg 2', 'parent' => null, 'created_at' => null, 'updated_at'=> null],
            '003' => ['code' => '003', 'name' => 'Child Org 3', 'parent' => '001', 'created_at' => null, 'updated_at'=> null],
            '004' => ['code' => '004', 'name' => 'Child Org 4', 'parent' => '001', 'created_at' => null, 'updated_at'=> null],
            '005' => ['code' => '005', 'name' => 'Child Child 5', 'parent' => '004', 'created_at' => null, 'updated_at'=> null],
        ];

        $result = $this->service->getOrganizationsTree($items);

        $this->assertCount(2, $result);
        $this->assertCount(2, $result[0]['children']);
        $this->assertCount(0, $result[1]['children']);

        $checkItem = function(array $org, array $item) {
            $this->assertEquals($org['code'], $item['key']);
            $this->assertEquals($org['code'], $item['code']);
            $this->assertEquals("{$org['name']} ({$org['code']})", $item['label']);

            $this->assertEquals($org['code'], $item['data']['code']);
            $this->assertEquals($org['parent'], $item['data']['parent']);
            $this->assertEquals($org['name'], $item['data']['name']);
        };

        $checkItem($items['001'], $result[0]);
        $checkItem($items['002'], $result[1]);

        $checkItem($items['003'], $result[0]['children'][0]);
        $checkItem($items['004'], $result[0]['children'][1]);
        $checkItem($items['005'], $result[0]['children'][1]['children'][0]);
    }

    public function test_it_moves_orphaned_nodes_to_root(): void
    {
        $items = [
            '001' => ['code' => '001', 'name' => 'ParentOrg 1', 'parent' => null, 'created_at' => null, 'updated_at'=> null],
            '002' => ['code' => '002', 'name' => 'ParentOrg 2', 'parent' => '001', 'created_at' => null, 'updated_at'=> null],
            '003' => ['code' => '003', 'name' => 'Child Org 3', 'parent' => '006', 'created_at' => null, 'updated_at'=> null],
        ];

        $result = $this->service->getOrganizationsTree($items);
        $this->assertCount(2, $result);

        $this->assertEquals('003', $result[1]['code']);
    }


}
