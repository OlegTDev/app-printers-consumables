<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsumableTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Consumable $consumable;

    /** @var Printer[] */
    private array $printers = [];

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create(['code' => 'T0001', 'name' => 'Test Org']);

        $this->user = User::factory()->create([
            'name' => 'admin_test',
            'email' => 'admin_test@test.com',
            'fio' => 'Админ',
            'org_code' => 'T0001',
        ]);

        $this->consumable = Consumable::factory()->create([
            'type' => ConsumableTypesEnum::cartridge->name,
            'name' => 'Cartridge name',
            'color' => 'black',
            'id_author' => $this->user->id,
        ]);
        Consumable::factory()->createMany([
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Filter name', 'id_author' => $this->user->id],
            ['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Some', 'id_author' => $this->user->id],
            ['type' => ConsumableTypesEnum::other->name, 'name' => 'Other', 'id_author' => $this->user->id],
        ]);

        $this->printers = Printer::factory()->createMany([
            ['vendor' => 'HP', 'model' => 'Model_1', 'is_color_print' => true, 'id_author' => $this->user->id],
            ['vendor' => 'Xerox', 'model' => 'Model_2', 'is_color_print' => false, 'id_author' => $this->user->id],
        ])->keyBy('model')->all();

        $this->consumable->printers()->attach([$this->printers['Model_1']->id => ['id_author' => $this->user->id]]);
    }

    public function test_it_has_relation_author(): void
    {
        $this->assertTrue($this->user->is($this->consumable->author));
    }

    public function test_it_has_relation_printers(): void
    {
        /** @var \Illuminate\Support\Collection<Printer> */
        $printers = $this->consumable->printers;
        $this->assertCount(1, $printers);
        $this->assertEqualsCanonicalizing($this->printers['Model_1']->id, $printers->first()->id);
    }

    public function test_it_has_relation_not_printers(): void
    {
        /** @var \Illuminate\Support\Collection<Printer> */
        $printers = $this->consumable->printersNotIn()->get();
        $this->assertCount(1, $printers);
        $this->assertEquals('Model_2', $printers->first()->model);
    }

    public function test_it_has_printers_workplaces(): void
    {
        /** @var PrinterWorkplace[] */
        $wsCreated = PrinterWorkplace::factory()->createMany([
            [
                'id_printer' => $this->printers['Model_1']->id,
                'org_code' => $this->organization->code,
                'location' => '1',
                'inventory_number' => '111',
                'id_author' => $this->user->id,
            ],
            [
                'id_printer' => $this->printers['Model_2']->id,
                'org_code' => $this->organization->code,
                'location' => '2',
                'inventory_number' => '222',
                'id_author' => $this->user->id,
            ],
        ])->all();

        /** @var \Illuminate\Support\Collection<PrinterWorkplace> */
        $wsPrinters = $this->consumable->printersWorkplaces;
        $this->assertCount(1, $wsPrinters);
        $this->assertTrue($wsPrinters->first()->is($wsCreated[0]));
    }

    public function test_it_has_consumable_counts(): void
    {
        $this->assertEmpty($this->consumable->consumablesCount);
        $this->assertEmpty($this->consumable->counts);

        /** @var ConsumableCount[] */
        $consumableCounts = ConsumableCount::factory()->createMany([
            ['id_consumable' => $this->consumable->id, 'count' => 5],
            ['id_consumable' => $this->consumable->id, 'count' => 10],
        ])->keyBy('count')->all();

        $this->consumable->refresh();
        $this->assertNotEmpty($this->consumable->consumablesCount);
        $this->assertCount(2, $this->consumable->consumablesCount);
        $this->assertNotEmpty($this->consumable->counts);
        $this->assertCount(2, $this->consumable->counts);
    }

    public function test_it_has_consumable_counts_by_current_organization(): void
    {
        Auth::login($this->user);

        $this->assertNull($this->consumable->consumableCountCurrentOrganization);

        /** @var ConsumableCount[] */
        $consumableCounts = ConsumableCount::factory()->createMany([
            ['id_consumable' => $this->consumable->id, 'count' => 5],
            ['id_consumable' => $this->consumable->id, 'count' => 10],
        ])->keyBy('count')->all();

        Organization::factory()->create(['code' => 'T0002', 'name' => 'Test Org 2']);
        DB::table('consumables_counts_organizations')->insert([
            ['id_consumable_count' => $consumableCounts['5']->id, 'org_code' => 'T0001'],
            ['id_consumable_count' => $consumableCounts['10']->id, 'org_code' => 'T0002'],
        ]);

        $this->consumable->refresh();
        $this->assertNotNull($this->consumable->consumableCountCurrentOrganization);
        $this->assertTrue($consumableCounts['5']->is($this->consumable->consumableCountCurrentOrganization));
    }

    public function test_it_filtered(): void
    {
        $foundByName = Consumable::query()->filter(['search' => 'name'])->pluck('name')->toArray();
        $this->assertCount(2, $foundByName);
        $this->assertEqualsCanonicalizing(['Filter name', 'Cartridge name'], $foundByName);

        $someFound = Consumable::query()->filter(['search' => 'some'])->pluck('name')->toArray();
        $this->assertCount(1, $someFound);
        $this->assertEqualsCanonicalizing(['Some'], $someFound);

        $notFound = Consumable::query()->filter(['search' => 'not-found-name'])->pluck('name')->toArray();
        $this->assertEmpty($notFound);
    }

    public function test_it_filter_with_other_types_by_printer(): void
    {
        $idPrinter = $this->printers['Model_1']->id;
        $filteredNotFound = Consumable::query()->with(['printers'])->withOtherTypesByPrinter($idPrinter)->first();
        $this->assertNull($filteredNotFound);

        $consumableOther = Consumable::query()->where('type', ConsumableTypesEnum::other->name)->firstOrFail();
        $consumableOther->printers()->attach([$idPrinter => ['id_author' => $this->user->id]]);

        $filteredFound = Consumable::query()->with(['printers'])->withOtherTypesByPrinter($idPrinter)->first();
        $this->assertNotNull($filteredFound);
        $this->assertTrue($consumableOther->is($filteredFound));
    }

    public function test_it_title(): void
    {
        $consumableCartridge = Consumable::query()->where('type', ConsumableTypesEnum::cartridge->name)->firstOrFail();
        $consumableOther = Consumable::query()->where('type', ConsumableTypesEnum::other->name)->firstOrFail();

        $color = CartridgeColors::getNameByColor($consumableCartridge->color);
        $this->assertEquals("Картридж {$consumableCartridge->name} ($color)", $consumableCartridge->title());
        $this->assertEquals("Другое {$consumableOther->name}", $consumableOther->title());
    }

    public function test_it_scoped_without_other_types_by_printer(): void
    {
        $founded1 = Consumable::query()->withoutOtherTypesByPrinter()->pluck('type')->toArray();
        $this->assertCount(3, $founded1);
        $this->assertEqualsCanonicalizing(array_fill(0, 3, 'cartridge'), $founded1);

        $printer = $this->printers['Model_1'];
        $consumableOther = Consumable::query()->where('type', ConsumableTypesEnum::other->name)->firstOrFail();
        $consumableOther->printers()->attach([$printer->id => ['id_author' => $this->user->id]]);

        $founded2 = Consumable::query()->withoutOtherTypesByPrinter()->pluck('type')->toArray();
        $this->assertCount(3, $founded2);
        $this->assertEqualsCanonicalizing(array_fill(0, 3, 'cartridge'), $founded2);
    }

}
