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

class ConsumableCountAddedTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Organization::factory()->create(['code' => 'T0001', 'name' => 'Test Org']);
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

    public function test_it_belongs_to_parent_consumable_count(): void
    {
        $consumable = $this->createConsumable();
        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 0]);

        /** @var ConsumableCountAdded */
        $consumableCountAdded = ConsumableCountAdded::factory()->for($consumableCount)->create([
            'id_author' => $this->adminUser->id,
            'count' => 0,
        ]);

        $this->assertTrue($consumableCountAdded->consumableCount->is($consumableCount));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $consumableCountAdded->consumableCount());
    }

    public function test_it_belongs_to_parent_author(): void
    {
        $consumable = $this->createConsumable();
        /** @var ConsumableCount */
        $consumableCount = ConsumableCount::factory()->for($consumable)->create(['count' => 0]);

        /** @var ConsumableCountAdded */
        $consumableCountAdded = ConsumableCountAdded::factory()->for($consumableCount)->create([
            'id_author' => $this->adminUser->id,
            'count' => 0,
        ]);

        $this->assertTrue($this->adminUser->is($consumableCountAdded->author));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $consumableCountAdded->author());
    }

}
