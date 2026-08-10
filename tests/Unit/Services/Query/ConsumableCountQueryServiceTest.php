<?php

namespace Tests\Unit\Policies;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use App\Services\Query\ConsumableCountQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableCountQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConsumableCountQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(ConsumableCountQueryService::class);
    }


    public function test_it_get_consumable_counts_by_printer_workplace_and_organization(): void
    {
        $organization = Organization::factory()->create(['code' => '001']);
        $otherOrganization = Organization::factory()->create();
        $user = User::factory()->create(['org_code' => $organization]);

        $consumable = Consumable::factory()->create(['id_author' => $user]);
        $consumableCount = ConsumableCount::factory()->for($consumable)->create();
        $consumableCount->organizations()->attach([$organization->code]);

        $printer = Printer::factory()->create(['id_author' => $user]);
        PrinterWorkplace::factory()->for($printer)->create(['org_code' => $organization]);
        $printer->consumables()->attach($consumable->id, ['id_author' => $user->id]);


        Printer::factory()->count(3)->create(['id_author' => $user])->each(function(Printer $p) use ($otherOrganization, $consumable, $user) {
            PrinterWorkplace::factory()->for($p)->create(['org_code' => $otherOrganization]);
            $p->consumables()->attach($consumable->id, ['id_author' => $user->id]);
        });
        Printer::factory()->count(3)->create(['id_author' => $user])->each(function(Printer $p) use ($organization, $consumable, $user) {
            PrinterWorkplace::factory()->for($p)->create(['org_code' => $organization]);
            $p->consumables()->attach($consumable->id, ['id_author' => $user->id]);
        });

        $result = $this->service->getConsumableCountByPrinterWorkplace($printer->id, $organization->code);
        $first = $result->first();

        $this->assertCount(1, $result);
        $this->assertEquals($first->id, $consumableCount->id);
        $this->assertEquals($first->id_consumable, $consumable->id);
        $this->assertEquals($first->name, $consumable->name);
    }

    public function test_it_returns_correct_data_with_row_number_and_sorting(): void
    {
        $user = User::factory()->create();
        $org1 = Organization::factory()->create(['code' => '001']);
        $org2 = Organization::factory()->create(['code' => '002']);

        $cartridge = Consumable::factory()->create(['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Cartridge', 'id_author' => $user]);
        $drum = Consumable::factory()->create(['type' => ConsumableTypesEnum::drum->name, 'name' => 'Drum', 'id_author' => $user]);

        $countCartridge1 = ConsumableCount::factory()->for($cartridge)->create();
        $countCartridge1->organizations()->attach([$org1->code]);
        $countCartridge2 = ConsumableCount::factory()->for($cartridge)->create();
        $countCartridge2->organizations()->attach([$org2->code]);

        $countDrum = ConsumableCount::factory()->for($drum)->create();
        $countDrum->organizations()->attach([$org1->code]);

        $query = $this->service->buildConsumableCountByOrganizations([$org1->code, $org2->code]);
        $result = $query->get();

        $this->assertCount(3, $result);
        $this->assertEquals(1, $result->first()->row_num);

        $rows = $result->toArray();

        $this->assertEquals(1, $rows[0]['row_num']);
        $this->assertEquals($org1->code, $rows[0]['org_code']);
        $this->assertEquals($countCartridge1->count, $rows[0]['count']);
        $this->assertEquals($cartridge->type, $rows[0]['type']);
        $this->assertEquals($cartridge->name, $rows[0]['name']);

        $this->assertEquals(2, $rows[1]['row_num']);
        $this->assertEquals($org1->code, $rows[1]['org_code']);
        $this->assertEquals($countDrum->count, $rows[1]['count']);
        $this->assertEquals($drum->type, $rows[1]['type']);
        $this->assertEquals($drum->name, $rows[1]['name']);

        $this->assertEquals(3, $rows[2]['row_num']);
        $this->assertEquals($org2->code, $rows[2]['org_code']);
        $this->assertEquals($countCartridge2->count, $rows[2]['count']);
        $this->assertEquals($cartridge->type, $rows[2]['type']);
        $this->assertEquals($cartridge->name, $rows[2]['name']);
    }

    public function test_it_filters_by_organizations_codes(): void
    {
        $user = User::factory()->create();
        $org1 = Organization::factory()->create(['code' => '001']);
        $org2 = Organization::factory()->create(['code' => '002']);

        $cartridge = Consumable::factory()->create(['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Cartridge', 'id_author' => $user]);

        $countCartridge1 = ConsumableCount::factory()->for($cartridge)->create();
        $countCartridge1->organizations()->attach([$org1->code]);
        $countCartridge2 = ConsumableCount::factory()->for($cartridge)->create();
        $countCartridge2->organizations()->attach([$org2->code]);

        $queryUnknownOrg = $this->service->buildConsumableCountByOrganizations(['9999']);
        $resultUnknown = $queryUnknownOrg->get();
        $this->assertCount(0, $resultUnknown);

        $queryOrg1 = $this->service->buildConsumableCountByOrganizations([$org1->code]);
        $resultOrg1 = $queryOrg1->get();
        $this->assertCount(1, $resultOrg1);
        $this->assertEquals($org1->code, $resultOrg1->first()->org_code);
        $this->assertEquals($countCartridge1->count, $resultOrg1->first()->count);

        $queryOrg2 = $this->service->buildConsumableCountByOrganizations([$org2->code]);
        $resultOrg2 = $queryOrg2->get();
        $this->assertCount(1, $resultOrg2);
        $this->assertEquals($org2->code, $resultOrg2->first()->org_code);
        $this->assertEquals($countCartridge2->count, $resultOrg2->first()->count);
    }

    public function test_it_returns_separate_rows_for_each_organization_link(): void
    {
        $user = User::factory()->create();
        $org1 = Organization::factory()->create(['code' => '001']);
        $org2 = Organization::factory()->create(['code' => '002']);

        $consumable = Consumable::factory()->create(['id_author' => $user->id]);
        $count = ConsumableCount::factory()->for($consumable)->create(['count' => 10]);

        $count->organizations()->attach([$org1->code, $org2->code]);

        $query = $this->service->buildConsumableCountByOrganizations([$org1->code, $org2->code]);
        $result = $query->get();

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing([$org1->code, $org2->code], $result->pluck('org_code')->all());

        $this->assertEquals($count->count, $result->first()->count);
        $this->assertEquals($count->count, $result->last()->count);
    }

    public function test_it_selects_only_required_columns(): void
    {
        $org = Organization::factory()->create(['code' => '005']);
        $consumable = Consumable::factory()->create(['name' => 'Test Item', 'type' => 'test', 'color' => 'red', 'description' => 'desc']);

        $count = ConsumableCount::factory()->for($consumable)->create(['count' => 5]);
        $count->organizations()->attach($org->code);

        $query = $this->service->buildConsumableCountByOrganizations(['005']);
        $result = $query->get();

        $row = $result->first();

        $this->assertNotNull($row->row_num);

        $this->assertNotNull($row->count);
        $this->assertNotNull($row->org_code);
        $this->assertNotNull($row->type);
        $this->assertNotNull($row->name);
        $this->assertNotNull($row->color);
        $this->assertNotNull($row->description);

        $this->assertFalse(isset($row->id));
    }


}
