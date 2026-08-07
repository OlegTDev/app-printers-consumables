<?php

namespace Tests\Unit\Policies;

use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Organization;
use App\Models\Printer\PrinterWorkplace;
use App\Services\Query\ConsumableCountInstalledQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableCountInstalledQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConsumableCountInstalledQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(ConsumableCountInstalledQueryService::class);
    }

    public function test_it_returns_consumables_with_installed_counts_filtered_by_organizations_and_date_range(): void
    {
        $organization1 = Organization::factory()->create();
        $organization2 = Organization::factory()->create();

        $consumable = Consumable::factory()->create();

        $generate = function(Organization $org, Consumable $consumable, array $consumableCountInstalleds) {
            $consumableCount = tap(ConsumableCount::factory()->for($consumable)->create(['count' => 10]), fn($consumableCount) =>
                $consumableCount->organizations()->attach([$org->code])
            );

            foreach ($consumableCountInstalleds as $consumableCountInstalled) {
                ConsumableCountInstalled::factory()
                    ->for($consumableCount)
                    ->for(PrinterWorkplace::factory()->for($org)->create())
                    ->create(['count' => $consumableCountInstalled['count'], 'created_at' => $consumableCountInstalled['created_at']]);
            }
        };

        $generate(
            org: $organization1,
            consumable: $consumable,
            consumableCountInstalleds: [
                ['count' => 2, 'created_at' => now()->subDays(2)],
                ['count' => 3, 'created_at' => now()->subDays(1)],
            ],
        );

        $generate(
            org: $organization2,
            consumable: $consumable,
            consumableCountInstalleds: [['count' => 4, 'created_at' => now()->subDays(10)]],
        );

        $query = $this->service->buildCountInstalled(
            organizations: [$organization1->code],
            dateFrom: now()->subDays(3)->toDateString(),
            dateTo: now()->toDateString(),
        );

        $result = $query->get();

        $this->assertCount(1, $result);
        $row = $result->first();

        $this->assertEquals($organization1->code, $row['org_code']);
        $this->assertEquals(5, (int) $row['count_installed']);
        $this->assertEquals(10, (int) $row['count_now']);
        $this->assertEquals($consumable->type, $row['type']);
        $this->assertEquals($consumable->name, $row['name']);
    }


}
