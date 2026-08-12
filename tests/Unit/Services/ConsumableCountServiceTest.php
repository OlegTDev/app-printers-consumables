<?php

namespace Tests\Unit\Services;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Organization;
use App\Models\Printer\PrinterWorkplace;
use App\Services\Consumables\ConsumableCountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableCountServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConsumableCountService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(ConsumableCountService::class);
    }

    public function test_it_added_with_empty_consumable_count(): void
    {
        $orgCode = '0001';

        /** @var User */
        $user = User::factory()->create(['org_code' => Organization::factory()->create(['code' => $orgCode])]);
        /** @var Consumable */
        $consumable = Consumable::factory()->for($user, 'author')->create();

        $this->assertEmpty($consumable->consumablesCount);

        $this->service->add(
            idConsumable: $consumable->id,
            count: 10,
            idUser: $user->id,
            findOrgCode: $orgCode,
            organizations: [$orgCode],
        );

        $consumable->refresh();
        $this->assertCount(1, $consumable->consumablesCount);
        $this->assertEquals(10, $consumable->consumablesCount->first()->count);
        $this->assertTrue($consumable->consumablesCount->first()->organizations->pluck('code')->contains($orgCode));
    }

    public function test_it_added_with_exists_consumable_count_record(): void
    {
        $orgCode = '0001';

        /** @var User */
        $user = User::factory()->create(['org_code' => Organization::factory()->create(['code' => $orgCode])]);
        /** @var Consumable */
        $consumable = Consumable::factory()->for($user, 'author')->create();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 5]);
        $consumableCount->organizations()->attach(['0001']);

        $this->service->add(
            idConsumable: $consumable->id,
            count: 10,
            idUser: $user->id,
            findOrgCode: $orgCode,
            organizations: [$orgCode],
        );

        $consumable->refresh();
        $this->assertEquals(15, $consumable->consumablesCount->first()->count);
        $this->assertTrue($consumable->consumablesCount->first()->organizations->pluck('code')->contains($orgCode));
    }

    public function test_it_update_consumable_count(): void
    {
        $orgCode = '0001';

        /** @var User */
        $user = User::factory()->create(['org_code' => Organization::factory()->create(['code' => $orgCode])]);
        /** @var Consumable */
        $consumable = Consumable::factory()->for($user, 'author')->create();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 5]);
        $consumableCount->organizations()->attach([$orgCode]);

        $this->assertEmpty($consumableCount->consumablesAdded);
        $this->service->update($consumableCount->id, 10, $consumableCount->consumable->author->id);

        $consumableCount->refresh();
        /** @var ConsumableCountAdded */
        $consumableCountAdded = $consumableCount->consumablesAdded->first();
        $this->assertEquals(10, $consumableCountAdded->count);
        $this->assertEquals(15, $consumableCount->count);
    }

    public function test_it_installed_consumable(): void
    {
        $orgCode = '0001';

        /** @var User */
        $user = User::factory()->create(['org_code' => Organization::factory()->create(['code' => $orgCode])]);
        /** @var Consumable */
        $consumable = Consumable::factory()->for($user, 'author')->create();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 15]);
        $consumableCount->organizations()->attach([$orgCode]);

        $this->assertEmpty($consumableCount->consumablesInstalled);
        $printWs = PrinterWorkplace::factory()->create();
        $this->service->install($consumableCount->id, $printWs->id, 10, $user->id);

        $consumableCount->refresh();
        /** @var ConsumableCountInstalled */
        $consumableCountInstalled = $consumableCount->consumablesInstalled->first();
        $this->assertEquals(10, $consumableCountInstalled->count);
        $this->assertEquals(5, $consumableCount->count);
    }

    public function test_it_remove_added_consumable(): void
    {
        $orgCode = '0001';

        /** @var User */
        $user = User::factory()->create(['org_code' => Organization::factory()->create(['code' => $orgCode])]);
        /** @var Consumable */
        $consumable = Consumable::factory()->for($user, 'author')->create();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 15]);
        $consumableCount->organizations()->attach([$orgCode]);

        /** @var ConsumableCountAdded */
        $consumableCountAdded = ConsumableCountAdded::factory()
            ->for($consumableCount)
            ->for($user, 'author')
            ->create(['count' => 2]);


        $this->assertCount(1, $consumableCount->consumablesAdded);
        $this->assertEquals(15, $consumableCount->count);
        $this->assertTrue($consumableCountAdded->is($consumableCount->consumablesAdded->first()));

        $this->service->removeConsumableCountAdded($consumableCountAdded);

        $consumableCount->refresh();
        $this->assertEmpty($consumableCount->consumablesAdded);
        $this->assertEquals(13, $consumableCount->count);
    }

    public function test_it_remove_installed_consumable(): void
    {
        $orgCode = '0001';

        /** @var User */
        $user = User::factory()->create(['org_code' => Organization::factory()->create(['code' => $orgCode])]);
        /** @var Consumable */
        $consumable = Consumable::factory()->for($user, 'author')->create();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 15]);
        $consumableCount->organizations()->attach([$orgCode]);

        /** @var ConsumableCountInstalled */
        $consumableCountInstalled = ConsumableCountInstalled::factory()
            ->for($consumableCount)
            ->for($user, 'author')
            ->create(['count' => 2]);

        $this->assertCount(1, $consumableCount->consumablesInstalled);
        $this->assertEquals(15, $consumableCount->count);
        $this->assertTrue($consumableCountInstalled->is($consumableCount->consumablesInstalled->first()));

        $this->service->removeConsumableCountInstalled($consumableCountInstalled);

        $consumableCount->refresh();
        $this->assertEmpty($consumableCount->consumablesInstalled);
        $this->assertEquals(17, $consumableCount->count);
    }

    public function test_it_correct_consumable_count(): void
    {
        $orgCode = '0001';

        /** @var User */
        $user = User::factory()->create(['org_code' => Organization::factory()->create(['code' => $orgCode])]);
        /** @var Consumable */
        $consumable = Consumable::factory()->for($user, 'author')->create();

        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 15]);
        $consumableCount->organizations()->attach([$orgCode]);

        $this->assertEquals(15, $consumableCount->count);

        $this->service->correctBalance($consumableCount, 10);

        $consumableCount->refresh();
        $this->assertEquals(10, $consumableCount->count);
    }



}
