<?php

namespace Tests\Unit\Policies;

use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Organization;
use App\Models\Printer\PrinterWorkplace;
use App\Services\Query\ChartQueryService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChartQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(ChartQueryService::class);
    }

    public function test_it_returns_last_consumables_added_grouped_by_date(): void
    {
        [$organization, $firstConsumableCount, $secondConsumableCount] = $this->createConsumableCountsWithOrganization();

        ConsumableCountAdded::factory()
            ->for($firstConsumableCount)
            ->create(['count' => 10, 'created_at' => now()->subDays(2)->setHour(10)]);
        ConsumableCountAdded::factory()
            ->for($firstConsumableCount)
            ->create(['count' => 5, 'created_at' => now()->subDays(2)->setHour(14)]);

        ConsumableCountAdded::factory()
            ->for($secondConsumableCount)
            ->create(['count' => 7,  'created_at' => now()->subDays(1)->setHour(9)]);
        ConsumableCountAdded::factory()
            ->for($secondConsumableCount)
            ->create(['count' => 3,  'created_at' => now()->subDays(1)->setHour(16)]);


        $result = $this->service->getLastConsumablesAdded($organization->code, 30);
        $expected = [
            (object) [
                'date' => now()->subDays(2)->toDateString(),
                'count' => '15',
            ],
            (object) [
                'date' => now()->subDays(1)->toDateString(),
                'count' => '10',
            ],
        ];

        $this->assertCount(2, $result);
        $this->assertEquals($expected, $result);
    }


    public function test_it_returns_last_consumables_added_limited(): void
    {
        [$organization, $firstConsumableCount, $secondConsumableCount] = $this->createConsumableCountsWithOrganization();

        ConsumableCountAdded::factory()
            ->for($firstConsumableCount)
            ->create(['count' => 10, 'created_at' => now()->subDays(2)->setHour(10)]);
        ConsumableCountAdded::factory()
            ->for($firstConsumableCount)
            ->create(['count' => 5, 'created_at' => now()->subDays(2)->setHour(14)]);

        ConsumableCountAdded::factory()
            ->for($secondConsumableCount)
            ->create(['count' => 7,  'created_at' => now()->subDays(1)->setHour(9)]);
        ConsumableCountAdded::factory()
            ->for($secondConsumableCount)
            ->create(['count' => 3,  'created_at' => now()->subDays(1)->setHour(16)]);


        $result = $this->service->getLastConsumablesAdded($organization->code, 1);
        $expected = [
            (object) [
                'date' => now()->subDays(2)->toDateString(),
                'count' => '15',
            ],
        ];

        $this->assertCount(1, $result);
        $this->assertEquals($expected, $result);
    }


    public function test_it_returns_last_consumables_installed_grouped_by_date(): void
    {
        [$organization, $firstConsumableCount, $secondConsumableCount] = $this->createConsumableCountsWithOrganization();

        $printerWs = PrinterWorkplace::factory()->create();

        ConsumableCountInstalled::factory()
            ->for($firstConsumableCount)
            ->for($printerWs)
            ->create(['count' => 10, 'created_at' => now()->subDays(2)->setHour(10)]);
        ConsumableCountInstalled::factory()
            ->for($firstConsumableCount)
            ->for($printerWs)
            ->create(['count' => 5, 'created_at' => now()->subDays(2)->setHour(14)]);

        ConsumableCountInstalled::factory()
            ->for($secondConsumableCount)
            ->for($printerWs)
            ->create(['count' => 7,  'created_at' => now()->subDays(1)->setHour(9)]);
        ConsumableCountInstalled::factory()
            ->for($secondConsumableCount)
            ->for($printerWs)
            ->create(['count' => 3,  'created_at' => now()->subDays(1)->setHour(16)]);


        $result = $this->service->getLastConsumablesInstalled($organization->code, 30);
        $expected = [
            (object) [
                'date' => now()->subDays(1)->toDateString(),
                'count' => '10',
            ],
            (object) [
                'date' => now()->subDays(2)->toDateString(),
                'count' => '15',
            ],
        ];
        $this->assertCount(2, $result);
        $this->assertEquals($expected, $result);
    }

    public function test_it_returns_last_consumables_installed_limited(): void
    {
        [$organization, $firstConsumableCount, $secondConsumableCount] = $this->createConsumableCountsWithOrganization();

        $printerWs = PrinterWorkplace::factory()->create();

        ConsumableCountInstalled::factory()
            ->for($firstConsumableCount)
            ->for($printerWs)
            ->create(['count' => 10, 'created_at' => now()->subDays(2)->setHour(10)]);
        ConsumableCountInstalled::factory()
            ->for($firstConsumableCount)
            ->for($printerWs)
            ->create(['count' => 5, 'created_at' => now()->subDays(2)->setHour(14)]);

        ConsumableCountInstalled::factory()
            ->for($secondConsumableCount)
            ->for($printerWs)
            ->create(['count' => 7,  'created_at' => now()->subDays(1)->setHour(9)]);
        ConsumableCountInstalled::factory()
            ->for($secondConsumableCount)
            ->for($printerWs)
            ->create(['count' => 3,  'created_at' => now()->subDays(1)->setHour(16)]);


        $result = $this->service->getLastConsumablesInstalled($organization->code, 1);
        $expected = [
            (object) [
                'date' => now()->subDays(1)->toDateString(),
                'count' => '10',
            ],
        ];
        $this->assertCount(1, $result);
        $this->assertEquals($expected, $result);
    }


    /**
     * @return array<ConsumableCount|Organization>
     */
    private function createConsumableCountsWithOrganization(
        int $firstCount = 15, ?CarbonInterface $firstCreatedAt = null,
        int $secondCount = 10, ?CarbonInterface $secondCreatedAt = null,
    ): array
    {
        /** @var Organization */
        $organization = Organization::factory()->create();

        /** @var Consumable */
        $consumable = Consumable::factory()->create();

        /** @var ConsumableCount */
        $firstConsumableCount = ConsumableCount::factory()
            ->for($consumable)
            ->create(['count' => $firstCount, 'created_at' => $firstCreatedAt ?? now()->subDays(2), 'updated_at' => now()]);
        $firstConsumableCount->organizations()->attach([$organization->code]);

        /** @var ConsumableCount */
        $secondConsumableCount = ConsumableCount::factory()
            ->for($consumable)
            ->create(['count' => $secondCount, 'created_at' => $secondCreatedAt ?? now()->subDays(1), 'updated_at' => now()]);
        $secondConsumableCount->organizations()->attach([$organization->code]);

        return [$organization, $firstConsumableCount, $secondConsumableCount];
    }


}
