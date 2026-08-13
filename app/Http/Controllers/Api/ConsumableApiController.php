<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ConsumableCountInstalledResource;
use App\Http\Resources\ConsumableCountResource;
use App\Http\Resources\ConsumableResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use App\Services\Query\ConsumableCountQueryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsumableApiController
{
    /**
     * @route GET dictionary/consumables/{printer}/other
     */
    public function otherConsumablesForPrinter(Printer $printer)
    {
        $items = Consumable::withOtherTypesByPrinter($printer->id)->get();
        return ConsumableResource::collection($items);
    }

    /**
     * @route GET dictionary/consumables/not-other
     */
    public function notOtherConsumablesForPrinter()
    {
        $items = Consumable::withoutOtherTypesByPrinter()->get();
        return ConsumableResource::collection($items);
    }

    /**
     * @route GET /consumables/counts/installed/last
     */
    public function lastConsumableCountInstalled(): array
    {
        $limit = config('per_page.consumable_last_installed', 30);

        $query = ConsumableCountInstalled::with(['consumableCount.consumable', 'printerWorkplace.printer', 'author'])
            ->whereHas('printerWorkplace', fn(Builder $query) => $query->where('org_code', auth()->user()->org_code))
            ->orderByDesc('created_at')
            ->limit($limit);

        $data = ConsumableCountInstalledResource::collection($query->get());

        return [
            'data' => $data,
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ];
    }

    /**
     * @route GET /consumables/counts/list-by-printer/{printer}
     */
    public function listByPrinter(Printer $printer, ConsumableCountQueryService $consumableCountQueryService)
    {
        $orgCode = auth()->user()?->org_code;
        return
        [
            'consumables' => $consumableCountQueryService->getConsumableCountByPrinterWorkplace($printer->id, $orgCode),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
        ];
    }

    /**
     * @route GET /consumables/counts/by-consumable/{idConsumable}
     */
    public function showByConsumable(int $idConsumable): JsonResource
    {
        $consumableCount = ConsumableCount::where('id_consumable', $idConsumable)
            ->forCurrentUser()
            ->firstOrFail();

        return new ConsumableCountResource($consumableCount);
    }


}
