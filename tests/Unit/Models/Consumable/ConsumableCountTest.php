<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ConsumableCountTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create(['code' => 'T0001', 'name' => 'Test Org']);
        $this->adminUser = User::factory()->create(['name' => 'admin_test', 'email' => 'admin_test@test.com', 'fio' => 'Админ', 'org_code' => 'T0001']);
    }

    private function createConsumable(array $attributes = []): Consumable
    {
        return Consumable::factory()->create([
            'type' => ConsumableTypesEnum::cartridge->name,
            'name' => ConsumableTypesEnum::cartridge->value,
            'color' => 'black',
            'id_author' => $this->adminUser->id,
            ...$attributes,
        ]);
    }

    public function test_it_belongs_to_a_consumable(): void
    {
        /** @var Consumable */
        $consumable = Consumable::factory()->create([
            'type' => ConsumableTypesEnum::cartridge->name,
            'name' => ConsumableTypesEnum::cartridge->value,
            'color' => 'black',
            'id_author' => $this->adminUser->id,
        ]);
        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 5]);

        $this->assertEquals($consumableCount->id, $consumableCount->consumable->id);
    }

    public function test_it_has_many_consumable_count_added_records(): void
    {
        $consumable = $this->createConsumable();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 0]);

        /** @var ConsumableCountAdded */
        $addition1 = ConsumableCountAdded::factory()->for($consumableCount)->create(['id_author' => $this->adminUser->id, 'count' => 2]);
        /** @var ConsumableCountAdded */
        $addition2 = ConsumableCountAdded::factory()->for($consumableCount)->create(['id_author' => $this->adminUser->id, 'count' => 3]);

        $this->assertCount(2, $consumableCount->consumablesAdded);
        $this->assertEqualsCanonicalizing([$addition1->id, $addition2->id], $consumableCount->consumablesAdded->pluck('id')->all());
    }

    public function test_it_has_many_consumable_count_installed_records(): void
    {
        $consumable = $this->createConsumable();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 0]);

        $printer = Printer::factory()->create(['vendor' => 'HP', 'model' => 'Model 1', 'is_color_print' => true, 'id_author' => $this->adminUser->id]);
        $wsPrinter = PrinterWorkplace::factory()->for($printer)->create([
            'id_printer' => 1,
            'id_author' => $this->adminUser->id,
            'org_code' => 'T0001',
            'location' => '111',
            'inventory_number' => '000001',
        ]);

        $this->assertEmpty($consumableCount->consumablesInstalled);

        /** @var ConsumableCountInstalled */
        $installed1 = ConsumableCountInstalled::factory()
            ->for($consumableCount)
            ->for($wsPrinter)
            ->create(['id_author' => $this->adminUser->id, 'count' => 3]);

        /** @var ConsumableCountInstalled */
        $installed2 = ConsumableCountInstalled::factory()
            ->for($consumableCount)
            ->for($wsPrinter)
            ->create(['id_author' => $this->adminUser->id, 'count' => 2]);

        $consumableCount->refresh();

        $this->assertCount(2, $consumableCount->consumablesInstalled);
        $this->assertEqualsCanonicalizing([$installed1->id, $installed2->id], $consumableCount->consumablesInstalled->pluck('id')->all());
    }

    public function test_it_scope_by_current_user(): void
    {
        $org2 = Organization::factory()->create(['code' => 'T0002', 'name' => 'Org name 2']);

        $consumable = $this->createConsumable();
        /** @var ConsumableCount */
        $consumable1 = ConsumableCount::factory()->for($consumable)->create(['count' => 5]);
        /** @var ConsumableCount */
        $consumable2 = ConsumableCount::factory()->for($consumable)->create(['count' => 10]);

        $consumable1->organizations()->attach([$this->organization->code]);
        $consumable2->organizations()->attach([$org2->code]);

        Auth::login($this->adminUser);

        $filterWithCurrentUser = ConsumableCount::query()->forCurrentUser()->get();
        $this->assertCount(1, $filterWithCurrentUser);
        $this->assertEqualsCanonicalizing([$consumable1->id], $filterWithCurrentUser->pluck('id')->all());
        $this->assertFalse($filterWithCurrentUser->contains($consumable2));

        $filterWithoutCurrentUser = ConsumableCount::query()->get();
        $this->assertCount(2, $filterWithoutCurrentUser);
        $this->assertEqualsCanonicalizing([$consumable1->id, $consumable2->id], $filterWithoutCurrentUser->pluck('id')->all());
    }

    public function test_it_filter_by_search(): void
    {
        /** @var Consumable */
        $consumableOne = $this->createConsumable(['name' => 'Consumable 1']);
        /** @var Consumable */
        $consumableTwo = $this->createConsumable(['name' => 'Consumable 2']);

        /** @var \Illuminate\Support\Collection<ConsumableCount> */
        $consumablesCountsOne = ConsumableCount::factory()->for($consumableOne)->createMany([['count' => 1], ['count' => 2]]);
        /** @var \Illuminate\Support\Collection<ConsumableCount> */
        $consumablesCountsTwo = ConsumableCount::factory()->for($consumableTwo)->createMany([['count' => 3], ['count' => 4], ['count' => 2]]);

        $resultSearch1 = ConsumableCount::query()->filter(['search' => 'consumable'])->get();
        $expectedIds = $consumablesCountsOne->merge($consumablesCountsTwo)->pluck('id')->all();
        $this->assertEqualsCanonicalizing($expectedIds, $resultSearch1->pluck('id')->all());

        $resultSearch2 = ConsumableCount::query()->filter(['search' => 'not-found-text'])->get();
        $this->assertCount(0, $resultSearch2);
    }

    public function test_it_filter_by_consumable_type(): void
    {
        $consumableOne = $this->createConsumable(['type' => ConsumableTypesEnum::cartridge->name]);
        $consumableTwo = $this->createConsumable(['type' => ConsumableTypesEnum::drum->name]);
        $consumableThree = $this->createConsumable(['type' => ConsumableTypesEnum::cartridge->name, 'name' => 'Other cartridge']);

        /** @var ConsumableCount */
        $consumableCountOne = ConsumableCount::factory()->for($consumableOne)->create(['count' => 0]);
        /** @var ConsumableCount */
        $consumableCountTwo = ConsumableCount::factory()->for($consumableTwo)->create(['count' => 0]);
        /** @var ConsumableCount */
        $consumableCountThree = ConsumableCount::factory()->for($consumableThree)->create(['count' => 0]);

        $resultSearch1 = ConsumableCount::query()->filter(['consumableType' => ConsumableTypesEnum::cartridge->name])->get();
        $this->assertCount(2, $resultSearch1);
        $this->assertEqualsCanonicalizing([$consumableCountOne->id, $consumableCountThree->id], $resultSearch1->pluck('id')->all());

        $resultSearch2 = ConsumableCount::query()->filter(['consumableType' => ConsumableTypesEnum::drum->name])->get();
        $this->assertCount(1, $resultSearch2);
        $this->assertEqualsCanonicalizing([$consumableCountTwo->id], $resultSearch2->pluck('id')->all());

        $resultSearch3 = ConsumableCount::query()->filter(['consumableType' => ConsumableTypesEnum::wasteContainer->name])->get();
        $this->assertCount(0, $resultSearch3);
    }

    public function test_it_belongs_to_many_organization_records(): void
    {
        $consumable = $this->createConsumable();
        /** @var ConsumableCount */
        $consumableCount1 = ConsumableCount::factory()->for($consumable)->create(['count' => 0]);
        $consumableCount1->organizations()->attach([$this->organization->code]);
        /** @var ConsumableCount */
        $consumableCount2 = ConsumableCount::factory()->for($consumable)->create(['count' => 0]);

        $this->assertCount(1, $consumableCount1->organizations);
        $this->assertEquals([$this->organization->code], $consumableCount1->organizations->pluck('code')->all());

        $this->assertCount(0, $consumableCount2->organizations);
    }

}
