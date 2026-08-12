<?php

namespace Tests\Unit\Policies;


use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use App\Services\Query\PrintersWorkplaceQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintersWorkplaceQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private PrintersWorkplaceQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(PrintersWorkplaceQueryService::class);
    }

    public function test_it_returns_printer_workplaces_with_correct_counts_and_sorting(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['org_code' => $org->code]);

        $printer = Printer::factory()->create([
            'vendor' => 'HP',
            'model' => 'LaserJet Pro',
            'is_color_print' => false,
        ]);

        $pw = PrinterWorkplace::factory()
            ->for($printer)
            ->for($user, 'author')
            ->for($org)
            ->create([
                'location' => 'Office 101',
                'serial_number' => 'SN-123',
                'inventory_number' => 'INV-456',
            ]);

        $cartridge = Consumable::factory()->create(['type' => ConsumableTypesEnum::cartridge->name, 'id_author' => $user->id]);
        $drum = Consumable::factory()->create(['type' => ConsumableTypesEnum::drum->name, 'id_author' => $user->id]);
        $waste = Consumable::factory()->create(['type' => ConsumableTypesEnum::wasteContainer->name, 'id_author' => $user->id]);
        $other = Consumable::factory()->create(['type' => ConsumableTypesEnum::other->name, 'id_author' => $user->id]);

        $ccCartridge = ConsumableCount::factory()->for($cartridge)->create();
        $ccDrum = ConsumableCount::factory()->for($drum)->create();
        $ccWaste = ConsumableCount::factory()->for($waste)->create();
        $ccOther = ConsumableCount::factory()->for($other)->create();

        ConsumableCountInstalled::factory()->count(2)->for($ccCartridge)->for($pw)->for($user, 'author')->create(['count' => 1]);
        ConsumableCountInstalled::factory()->count(2)->for($ccDrum)->for($pw)->for($user, 'author')->create(['count' => 1]);
        ConsumableCountInstalled::factory()->count(2)->for($ccWaste)->for($pw)->for($user, 'author')->create(['count' => 1]);
        ConsumableCountInstalled::factory()->count(2)->for($ccOther)->for($pw)->for($user, 'author')->create(['count' => 1]);

        $query = $this->service->buildPrintersWorkplaceInstalledConsumablesByOrganizationsAndPeriod(
            organizations: [$org->code],
            dateFrom: null,
            dateTo: null
        );
        $result = $query->get();

        $this->assertCount(1, $result);

        $row = $result->first();

        // Проверка полей принтера и рабочего места
        $this->assertEquals($org->code, $row->org_code);
        $this->assertEquals('HP', $row->vendor);
        $this->assertEquals('LaserJet Pro', $row->model);
        $this->assertEquals('Office 101', $row->location);

        // Проверка подсчетов (по 2 штуки каждого типа)
        $this->assertEquals(2, (int) $row->count_cartridge);
        $this->assertEquals(2, (int) $row->count_drum);
        $this->assertEquals(2, (int) $row->count_waste_container);

        // Проверка логики "Other": это все типы, кроме cartridge, drum, wasteContainer
        // У нас был тип 'other', значит count_other тоже должен быть 2
        $this->assertEquals(2, (int) $row->count_other);

        // Проверка ROW_NUMBER
        $this->assertEquals(1, $row->row_num);

    }

    public function test_it_filters_installed_counts_by_date_range(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['org_code' => $org->code]);

        $printer = Printer::factory()->create(['vendor' => 'Canon', 'model' => 'i-SENSYS']);
        $pw = PrinterWorkplace::factory()
            ->for($printer)
            ->for($user, 'author')
            ->for($org)
            ->create([
                'location' => 'Office 101',
                'serial_number' => 'SN-123',
                'inventory_number' => 'INV-456',
            ]);

        $cartridge = Consumable::factory()->create(['type' => ConsumableTypesEnum::cartridge->name, 'id_author' => $user->id]);
        $cc = ConsumableCount::factory()->for($cartridge)->create();

        // До периода
        ConsumableCountInstalled::factory()->for($cc)->for($pw)->for($user, 'author')->create(['count' => 1, 'created_at' => now()->subDays(10)]);
        // В периоде
        ConsumableCountInstalled::factory()->for($cc)->for($pw)->for($user, 'author')->create(['count' => 5, 'created_at' => now()->subDays(1)]);
        // После периода
        ConsumableCountInstalled::factory()->for($cc)->for($pw)->for($user, 'author')->create(['count' => 10, 'created_at' => now()->addDays(5)]);

        $dateFrom = now()->subDays(2)->toDateString();
        $dateTo = now()->toDateString();

        $query = $this->service->buildPrintersWorkplaceInstalledConsumablesByOrganizationsAndPeriod(
            organizations: [$org->code],
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $row = $result->first();
        $this->assertEquals(5, (int) $row->count_cartridge);
        $this->assertEquals(0, (int) $row->count_drum);
    }

    public function test_it_correctly_calculates_other_type_as_exclusion_of_specific_types(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['org_code' => $org->code]);

        $printer = Printer::factory()->create(['vendor' => 'Canon', 'model' => 'i-SENSYS']);
        $pw = PrinterWorkplace::factory()
            ->for($printer)
            ->for($user, 'author')
            ->for($org)
            ->create([
                'location' => 'Office 101',
                'serial_number' => 'SN-123',
                'inventory_number' => 'INV-456',
            ]);

        $cartridge = Consumable::factory()->create(['type' => ConsumableTypesEnum::cartridge->name, 'id_author' => $user->id]);
        $drum = Consumable::factory()->create(['type' => ConsumableTypesEnum::drum->name, 'id_author' => $user->id]);
        $waste = Consumable::factory()->create(['type' => ConsumableTypesEnum::wasteContainer->name, 'id_author' => $user->id]);
        $toner = Consumable::factory()->create(['type' => ConsumableTypesEnum::other->name, 'name' => 'Toner', 'id_author' => $user->id]);
        $kit = Consumable::factory()->create(['type' => ConsumableTypesEnum::other->name, 'name' => 'Kit', 'id_author' => $user->id]);

        $ccCartridge = ConsumableCount::factory()->for($cartridge)->create();
        $ccDrum = ConsumableCount::factory()->for($drum)->create();
        $ccWaste = ConsumableCount::factory()->for($waste)->create();
        $ccToner = ConsumableCount::factory()->for($toner)->create();
        $ccKit = ConsumableCount::factory()->for($kit)->create();

        ConsumableCountInstalled::factory()->for($ccCartridge)->for($pw)->for($user, 'author')->create(['count' => 3]);
        ConsumableCountInstalled::factory()->for($ccDrum)->for($pw)->for($user, 'author')->create(['count' => 3]);
        ConsumableCountInstalled::factory()->for($ccWaste)->for($pw)->for($user, 'author')->create(['count' => 3]);
        ConsumableCountInstalled::factory()->for($ccToner)->for($pw)->for($user, 'author')->create(['count' => 3]);
        ConsumableCountInstalled::factory()->for($ccKit)->for($pw)->for($user, 'author')->create(['count' => 3]);

        $query = $this->service->buildPrintersWorkplaceInstalledConsumablesByOrganizationsAndPeriod(
            organizations: [$org->code],
            dateFrom: null,
            dateTo: null,
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $row = $result->first();

        $this->assertEquals(3, (int) $row->count_cartridge);
        $this->assertEquals(3, (int) $row->count_drum);
        $this->assertEquals(3, (int) $row->count_waste_container);

        $this->assertEquals(3 + 3, (int) $row->count_other);
    }

    public function test_it_return_empty_result_for_non_existent_organization(): void
    {
        $org = Organization::factory()->create(['code' => '001']);
        $user = User::factory()->create(['org_code' => $org->code]);

        $pw = PrinterWorkplace::factory()
            ->for(Printer::factory()->create())
            ->for($user, 'author')
            ->for($org)
            ->create();

        ConsumableCountInstalled::factory()
            ->count(5)
            ->for(ConsumableCount::factory()->for(Consumable::factory()->create())->create())
            ->for($pw)
            ->for($user, 'author')
            ->create();

        $query = $this->service->buildPrintersWorkplaceInstalledConsumablesByOrganizationsAndPeriod(
            organizations: ['002'],
            dateFrom: null,
            dateTo: null,
        );
        $result = $query->get();

        $this->assertCount(0, $result);
    }

    public function test_it_applies_correct_sorting_and_row_numbering(): void
    {
        $orgA = Organization::factory()->create(['code' => '00A']);
        $orgB = Organization::factory()->create(['code' => '00B']);

        $user = User::factory()->create(['org_code' => $orgA->code]);

        PrinterWorkplace::factory()
            ->for(Printer::factory()->create(['vendor' => 'ZZZ', 'model' => 'Model-Z']))
            ->for($user, 'author')
            ->for($orgA)
            ->create();

        PrinterWorkplace::factory()
            ->for(Printer::factory()->create(['vendor' => 'AAA', 'model' => 'Model-A']))
            ->for($user, 'author')
            ->for($orgA)
            ->create();

        PrinterWorkplace::factory()
            ->for(Printer::factory()->create(['vendor' => 'BBB', 'model' => 'Model-B']))
            ->for($user, 'author')
            ->for($orgB)
            ->create();

        $query = $this->service->buildPrintersWorkplaceInstalledConsumablesByOrganizationsAndPeriod(
            organizations: ['00A', '00B'],
            dateFrom: null,
            dateTo: null
        );

        $result = $query->get();

        $this->assertCount(3, $result);

        $rows = $result->toArray();

        $this->assertEquals($orgA->code, $rows[0]['org_code']);
        $this->assertEquals('AAA', $rows[0]['vendor']);
        $this->assertEquals(1, $rows[0]['row_num']);

        $this->assertEquals($orgA->code, $rows[1]['org_code']);
        $this->assertEquals('ZZZ', $rows[1]['vendor']);
        $this->assertEquals(2, $rows[1]['row_num']);

        $this->assertEquals($orgB->code, $rows[2]['org_code']);
        $this->assertEquals('BBB', $rows[2]['vendor']);
        $this->assertEquals(3, $rows[2]['row_num']);
    }


}
