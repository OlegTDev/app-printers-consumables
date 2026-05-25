<?php
declare(strict_types=1);

namespace App\Services\Consumables;

use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use Illuminate\Support\Facades\DB;

class ConsumableCountService
{

    public function add(int $idConsumable, bool $changeOrganization, array $organizations, int $count): int
    {
        return DB::transaction(function() use ($idConsumable, $changeOrganization, $organizations, $count) {
            $consumableCount = $this->firstOrCreateConsumableCount($idConsumable);

            if ($consumableCount->wasRecentlyCreated || $changeOrganization) {
                $consumableCount->organizations()->sync($organizations);
            }

            $this->createConsumableCountAdded($consumableCount, $count);
            return $consumableCount->id;
        });
    }

    private function firstOrCreateConsumableCount(int $idConsumable, int $count = 0): ConsumableCount
    {
        return ConsumableCount::firstOrCreate(['id_consumable' => $idConsumable], ['count' => $count]);
    }

    private function createConsumableCountAdded(ConsumableCount $consumableCount, int $count): ConsumableCountAdded
    {
        $model = new ConsumableCountAdded(['count' => $count]);
        $model->consumableCount()->associate($consumableCount);
        $model->saveOrFail();
        return $model;
    }
}
