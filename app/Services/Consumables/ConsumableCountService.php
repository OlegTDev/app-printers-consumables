<?php
declare(strict_types=1);

namespace App\Services\Consumables;

use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableCountInstalled;
use Illuminate\Support\Facades\DB;

class ConsumableCountService
{

    public function add(int $idConsumable, int $count, int $idUser, bool $changeOrganization, array $organizations = []): int
    {
        return DB::transaction(function() use ($idConsumable, $changeOrganization, $organizations, $count, $idUser) {
            $consumableCount = $this->firstOrCreateConsumableCount($idConsumable);

            if ($consumableCount->wasRecentlyCreated || $changeOrganization) {
                $consumableCount->organizations()->sync($organizations);
            }

            $this->createConsumableCountAdded($consumableCount, $count, $idUser);
            $this->incrementBalance($consumableCount, $count);
            return $consumableCount->id;
        });
    }

    public function install(int $idConsumableCount, int $idPrinterWorkplace, int $count, int $idUser): int
    {
        return DB::transaction(function() use ($idConsumableCount, $idPrinterWorkplace, $count, $idUser) {
            $consumableCount = $this->fistOrFailConsumableCount($idConsumableCount);

            $this->createConsumableCountInstalled($consumableCount, $count, $idPrinterWorkplace, $idUser);
            $this->decrementBalance($consumableCount, $count);

            return $consumableCount->id;
        });
    }

    public function removeConsumableCountAdded(ConsumableCountAdded $consumableCountAdded): void
    {
        DB::transaction(function () use ($consumableCountAdded) {
            $this->decrementBalance($consumableCountAdded->consumableCount, $consumableCountAdded->count);
            $consumableCountAdded->delete();
        });
    }

    public function removeConsumableCountInstalled(ConsumableCountInstalled $consumableCountInstalled): void
    {
        DB::transaction(function () use ($consumableCountInstalled) {
            $this->incrementBalance($consumableCountInstalled->consumableCount, $consumableCountInstalled->count);
            $consumableCountInstalled->delete();
        });
    }

    public function incrementBalance(ConsumableCount $consumableCount, int $count): int
    {
        return $consumableCount->increment('count', $count);
    }

    public function decrementBalance(ConsumableCount $consumableCount, int $count): int
    {
        return $consumableCount->decrement('count', $count);
    }

    public function correctBalance(ConsumableCount $consumableCount, int $count): void
    {
        $consumableCount->update(['count' => $count]);
    }

    private function firstOrCreateConsumableCount(int $idConsumable, int $count = 0): ConsumableCount
    {
        return ConsumableCount::firstOrCreate(['id_consumable' => $idConsumable], ['count' => $count]);
    }

    private function fistOrFailConsumableCount(int $idConsumableCount): ConsumableCount
    {
        return ConsumableCount::where(['id' => $idConsumableCount])->firstOrFail();
    }

    private function createConsumableCountAdded(ConsumableCount $consumableCount, int $count, int $idUser): ConsumableCountAdded
    {
        $model = new ConsumableCountAdded(['count' => $count, 'id_author' => $idUser]);
        $model->consumableCount()->associate($consumableCount);
        $model->saveOrFail();
        return $model;
    }

    private function createConsumableCountInstalled(
        ConsumableCount $consumableCount,
        int $count,
        int $idPrinterWorkplace,
        int $idUser,
    ): ConsumableCountInstalled
    {
        $model = new ConsumableCountInstalled([
            'id_printer_workplace' => $idPrinterWorkplace,
            'count' => $count,
            'id_author' => $idUser,
        ]);
        $model->consumableCount()->associate($consumableCount);
        $model->saveOrFail();
        return $model;
    }


}
