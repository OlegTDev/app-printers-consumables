<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PrinterTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create(['code' => 'T0001', 'name' => 'Test Org']);
        $this->adminUser = User::factory()->create(['name' => 'admin_test', 'email' => 'admin_test@test.com', 'fio' => 'Админ', 'org_code' => $this->organization]);
    }

    public function createConsumableCartridge(string $name = null, string $color = null): Consumable
    {
        return Consumable::factory()
            ->for($this->adminUser, 'author')
            ->create([
                'type' => ConsumableTypesEnum::cartridge,
                'name' => $name ?? 'Cartridge black',
                'color' => $color ?? 'black',
            ]);
    }

    private function createConsumableDrum(string $name = null): Consumable
    {
        return Consumable::factory()
            ->for($this->adminUser, 'author')
            ->create([
                'type' => ConsumableTypesEnum::drum,
                'name' => 'Drum',
            ]);
    }

    public function test_it_belongs_to_many_consumables(): void
    {
        $cartridge = $this->createConsumableCartridge();
        $drum = $this->createConsumableDrum();

        /** @var Printer */
        $printer = Printer::factory()
            ->for($this->adminUser, 'author')
            ->create(['vendor' => 'HP', 'model' => 'Model 1', 'is_color_print' => false]);

        $consumablesNull = $printer->consumables;
        $this->assertEmpty($consumablesNull);

        $printer->consumables()->syncWithoutDetaching([
            $cartridge->id => ['id_author' => $this->adminUser->id, 'created_at' => now(), 'updated_at'=> now()],
            $drum->id => ['id_author' => $this->adminUser->id, 'created_at' => now(), 'updated_at'=> now()],
        ]);
        $printer->refresh();
        $consumablesAll = $printer->consumables;
        $this->assertCount(2, $consumablesAll);
        $this->assertEqualsCanonicalizing([$cartridge->id, $drum->id], $consumablesAll->pluck('id')->all());
    }

    public function test_it_belong_to_parent_author(): void
    {
        /** @var Printer */
        $printer = Printer::factory()
            ->for($this->adminUser, 'author')
            ->create(['vendor' => 'HP', 'model' => 'Model 1', 'is_color_print' => false]);

        $this->assertTrue($this->adminUser->is($printer->author));
    }


    public function test_it_build_query_condition_not_linked_consumable(): void
    {
        $cartridge = $this->createConsumableCartridge();
        $drum = $this->createConsumableDrum();

        /** @var Printer */
        $printer = Printer::factory()
            ->for($this->adminUser, 'author')
            ->create(['vendor' => 'HP', 'model' => 'Model 1', 'is_color_print' => false]);

        $resultConsumablesAll = $printer->consumablesNotIn()->get();
        $this->assertCount(2, $resultConsumablesAll);

        $printer->consumables()->syncWithoutDetaching([
            $cartridge->id => ['id_author' => $this->adminUser->id, 'created_at' => now(), 'updated_at'=> now()],
        ]);

        $resultConsumablesNotIn = $printer->consumablesNotIn()->get();
        $this->assertCount(1, $resultConsumablesNotIn);
        $this->assertTrue($drum->is($resultConsumablesNotIn->first()));
    }

    public function test_it_has_many_linked_printers_workplaces(): void
    {
        /** @var Printer */
        $printerOne = Printer::factory()->for($this->adminUser, 'author')->create();
        /** @var PrinterWorkplace */
        $printerWorkplaceOne_1 = PrinterWorkplace::factory()
            ->for($printerOne)
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();
        /** @var PrinterWorkplace */
        $printerWorkplaceOne_2 = PrinterWorkplace::factory()
            ->for($printerOne)
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        /** @var Printer */
        $printerTwo = Printer::factory()->for($this->adminUser, 'author')->create();
        /** @var PrinterWorkplace */
        $printerWorkplaceTwo_1 = PrinterWorkplace::factory()
            ->for($printerTwo)
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        /** @var Printer */
        $printerThree = Printer::factory()->for($this->adminUser, 'author')->create();

        $this->assertCount(2, $printerOne->printersWorkplaces);
        $this->assertEqualsCanonicalizing([$printerWorkplaceOne_1->id, $printerWorkplaceOne_2->id], $printerOne->printersWorkplaces->pluck('id')->all());

        $this->assertCount(1, $printerTwo->printersWorkplaces);
        $this->assertEqualsCanonicalizing([$printerWorkplaceTwo_1->id], $printerTwo->printersWorkplaces->pluck('id')->all());

        $this->assertEmpty($printerThree->printersWorkplaces);
    }

    public function test_it_filtered_by_vendor_or_model(): void
    {
        $printers = Printer::factory()->for($this->adminUser, 'author')->createMany([
            ['vendor' => 'HP Printer', 'model' => 'Model 1'],
            ['vendor' => 'HP Printer', 'model' => 'Other'],
            ['vendor' => 'Xerox', 'model' => 'Model 2'],
        ]);

        $filterByVendor = Printer::query()->filter(['search' => 'HP'])->get();
        $this->assertCount(2, $filterByVendor);
        $this->assertEqualsCanonicalizing(['Model 1', 'Other'], $filterByVendor->pluck('model')->all());

        $filterByModel = Printer::query()->filter(['search' => 'Model'])->get();
        $this->assertCount(2, $filterByModel);
        $this->assertEqualsCanonicalizing(['HP Printer', 'Xerox'], $filterByModel->pluck('vendor')->all());

        $filterNotFound = Printer::query()->filter(['search' => 'not found text'])->get();
        $this->assertEmpty($filterNotFound);
    }




}
