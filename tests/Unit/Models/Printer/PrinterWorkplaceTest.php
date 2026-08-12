<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PrinterWorkplaceTest extends TestCase
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

    public function test_it_belongs_to_parent_printer(): void
    {
        /** @var Printer */
        $printer = Printer::factory()->for($this->adminUser, 'author')->create();
        /** @var PrinterWorkplace */
        $printerWs = PrinterWorkplace::factory()
            ->for($printer)
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $this->assertTrue($printer->is($printerWs->printer));
    }

    public function test_it_belongs_to_parent_organization(): void
    {
        /** @var PrinterWorkplace */
        $printerWs = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $this->assertTrue($this->organization->is($printerWs->organization));
    }

    public function test_it_belongs_to_parent_author(): void
    {
        /** @var PrinterWorkplace */
        $printerWs = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $this->assertTrue($this->adminUser->is($printerWs->author));
    }

    public function test_it_has_to_many_consumable_installeds(): void
    {
        /** @var PrinterWorkplace */
        $printerWs = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $this->assertEmpty($printerWs->consumableCountInstalled);


        /** @var Consumable */
        $consumable = Consumable::factory()
            ->for($this->adminUser, 'author')
            ->create([
                'type' => ConsumableTypesEnum::cartridge,
                'name' => 'Cartridge black',
                'color' => 'black',
            ]);

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 10]);


        $counts = ConsumableCountInstalled::factory()
            ->for($printerWs)
            ->for($consumableCount)
            ->for($this->adminUser, 'author')
            ->createMany([['count' => 3], ['count' => 7]])
            ->keyBy('count')->all();

        $printerWs->refresh();

        $this->assertCount(2, $printerWs->consumableCountInstalled);
        $this->assertEqualsCanonicalizing(array_keys($counts), $printerWs->consumableCountInstalled->pluck('count')->all());
    }

    public function test_it_belongs_to_consumables(): void
    {
        /** @var PrinterWorkplace */
        $printerWs = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $this->assertEmpty($printerWs->consumables);

        /** @var Consumable */
        $consumable1 = Consumable::factory()
            ->for($this->adminUser, 'author')
            ->create();
        /** @var Consumable */
        $consumable2 = Consumable::factory()
            ->for($this->adminUser, 'author')
            ->create();

        $printerWs->consumables()->attach([
            $consumable1->id => ['id_author' => $this->adminUser->id],
            $consumable2->id => ['id_author' => $this->adminUser->id],
        ]);

        $printerWs->refresh();

        $this->assertCount(2, $printerWs->consumables);
        $this->assertEqualsCanonicalizing([$consumable1->id, $consumable2->id], $printerWs->consumables->pluck('id')->all());
    }

    public function test_it_filter_by_current_user_by_organization(): void
    {
        $organization2 = Organization::factory()->create(['code' => 'T0002', 'name' => 'Test Org 2']);
        $user = User::factory()->create(['name' => 'user', 'email' => 'user@test.com', 'fio' => 'User', 'org_code' => $organization2]);

        /** @var PrinterWorkplace */
        $printerWs1 = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $printerWs2 = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        Auth::login($user);
        $this->assertCount(2, PrinterWorkplace::query()->get());
        $this->assertEmpty(PrinterWorkplace::query()->forCurrentUser()->get());

        Auth::login($this->adminUser);
        $resultWithFilter = PrinterWorkplace::query()->forCurrentUser()->get();
        $this->assertCount(2, $resultWithFilter);
        $this->assertEqualsCanonicalizing([$printerWs1->id, $printerWs2->id], $resultWithFilter->pluck('id')->all());
    }

    public function test_it_filter_printer_workplace_location_or_serial_number_or_inventory_number(): void
    {
        /** @var PrinterWorkplace */
        $printerWs1 = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create(['location' => '100', 'serial_number' => 'num_001', 'inventory_number' => '12345']);
        /** @var PrinterWorkplace */
        $printerWs2 = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create(['location' => '101', 'serial_number' => 'num_002', 'inventory_number' => '54321']);

        $resultByLocationLike = PrinterWorkplace::query()->filter(['search' => '10'])->get();
        $this->assertCount(2, $resultByLocationLike);
        $this->assertEqualsCanonicalizing([$printerWs1->id, $printerWs2->id], $resultByLocationLike->pluck('id')->all());

        $resultByLocationEquals = PrinterWorkplace::query()->filter(['search' => '100'])->get();
        $this->assertCount(1, $resultByLocationEquals);
        $this->assertEqualsCanonicalizing([$printerWs1->id], $resultByLocationEquals->pluck('id')->all());


        $resultBySerialNumberLike = PrinterWorkplace::query()->filter(['search' => 'num'])->get();
        $this->assertCount(2, $resultBySerialNumberLike);
        $this->assertEqualsCanonicalizing([$printerWs1->id, $printerWs2->id], $resultBySerialNumberLike->pluck('id')->all());

        $resultBySerialNumberEquals = PrinterWorkplace::query()->filter(['search' => 'num_002'])->get();
        $this->assertCount(1, $resultBySerialNumberEquals);
        $this->assertEqualsCanonicalizing([$printerWs2->id], $resultBySerialNumberEquals->pluck('id')->all());


        $resultByInventoryNumberLike = PrinterWorkplace::query()->filter(['search' => '5'])->get();
        $this->assertCount(2, $resultByInventoryNumberLike);
        $this->assertEqualsCanonicalizing([$printerWs1->id, $printerWs2->id], $resultByInventoryNumberLike->pluck('id')->all());

        $resultByInventoryNumberEquals = PrinterWorkplace::query()->filter(['search' => '12345'])->get();
        $this->assertCount(1, $resultByInventoryNumberEquals);
        $this->assertEqualsCanonicalizing([$printerWs1->id], $resultByInventoryNumberEquals->pluck('id')->all());
    }


    public function test_it_filter_printer_vendor_or_model(): void
    {
        /** @var Printer */
        $printer1 = Printer::factory()->for($this->adminUser, 'author')->create(['vendor' => 'HP Printer', 'model' => 'Model 1']);
        /** @var Printer */
        $printer2 = Printer::factory()->for($this->adminUser, 'author')->create(['vendor' => 'Xerox Printer', 'model' => 'Model 2']);

        /** @var PrinterWorkplace */
        $printerWs1 = PrinterWorkplace::factory()
            ->for($printer1)
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();
        /** @var PrinterWorkplace */
        $printerWs2 = PrinterWorkplace::factory()
            ->for($printer2)
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $resultByVendorLike = PrinterWorkplace::query()->filter(['search' => 'printer'])->get();
        $this->assertCount(2, $resultByVendorLike);
        $this->assertEqualsCanonicalizing([$printerWs1->id, $printerWs2->id], $resultByVendorLike->pluck('id')->all());

        $resultByVendorEquals = PrinterWorkplace::query()->filter(['search' => 'HP Printer'])->get();
        $this->assertCount(1, $resultByVendorEquals);
        $this->assertEqualsCanonicalizing([$printerWs1->id], $resultByVendorEquals->pluck('id')->all());


        $resultByModelLike = PrinterWorkplace::query()->filter(['search' => 'model'])->get();
        $this->assertCount(2, $resultByModelLike);
        $this->assertEqualsCanonicalizing([$printerWs1->id, $printerWs2->id], $resultByModelLike->pluck('id')->all());

        $resultByModelEquals = PrinterWorkplace::query()->filter(['search' => 'Model 2'])->get();
        $this->assertCount(1, $resultByModelEquals);
        $this->assertEqualsCanonicalizing([$printerWs2->id], $resultByModelEquals->pluck('id')->all());
    }

    public function test_it_filter_not_found_text(): void
    {
        PrinterWorkplace::factory()
            ->count(3)
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        PrinterWorkplace::factory()
            ->count(5)
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $this->assertEmpty(PrinterWorkplace::query()->filter(['search' => 'not-found-text'])->get());
    }

    public function test_it_automatically_set_author(): void
    {
        /** @var User */
        $user = User::factory()->create(['name' => 'user', 'email' => 'user@test.com', 'fio' => 'User', 'org_code' => $this->organization]);

        Auth::login($user);

        /** @var PrinterWorkplace */
        $printerWs = PrinterWorkplace::factory()
            ->for(Printer::factory()->for($this->adminUser, 'author')->create())
            ->for($this->adminUser, 'author')
            ->for($this->organization)
            ->create();

        $this->assertTrue($user->is($printerWs->author));
    }

}
