<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use Illuminate\Support\Facades\DB;

class OrderConsumableCountAddedService
{

    public function pushCount(int $idConsumable, string $orgCode, int $count, int $idAuthor): void
    {
        $idConsumableCount = $this->findOrCreateConsumableCount($idConsumable, $orgCode);
        ConsumableCountAdded::create([
            'id_consumable_count' => $idConsumableCount,
            'count' => $count,
            'id_author' => $idAuthor,
        ]);
    }

    private function findOrCreateConsumableCount(int $idConsumable, string $orgCode): int
    {
        return DB::transaction(function() use ($idConsumable, $orgCode) {
            $modelConsumableCount = ConsumableCount::firstOrCreate(['id_consumable' => $idConsumable], ['count' => 0]);
            if ($modelConsumableCount->wasRecentlyCreated) {
                DB::table('consumables_counts_organizations')->insert([
                    'id_consumable_count' => $modelConsumableCount->id,
                    'org_code' => $orgCode,
                ]);
            }
            return $modelConsumableCount->id;
        });
    }
}
