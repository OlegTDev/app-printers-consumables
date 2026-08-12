<?php

namespace Tests\Unit\Policies;


use App\Services\Query\ManufacturerQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ManufacturerQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private ManufacturerQueryService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(ManufacturerQueryService::class);
    }

    public function test_it_return_all_manufacturers(): void
    {
        DB::table('manufacturers')->insert([
            ['name' => 'HP', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Xerox', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Canon', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kyocera', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $result = $this->service->getAll();
        $this->assertCount(4, $result);
        $this->assertEqualsCanonicalizing(['HP', 'Xerox', 'Canon', 'Kyocera'], $result->pluck('name')->all());
    }


}
