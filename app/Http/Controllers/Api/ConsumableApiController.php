<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ConsumableCountInstalledResource;
use App\Http\Resources\ConsumableResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use Illuminate\Database\Eloquent\Builder;

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
        $items = Consumable::queryWithoutOtherTypesByPrinter()->get();
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
}
