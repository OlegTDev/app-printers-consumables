<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ConsumableResource;
use App\Models\Consumable\Consumable;
use App\Models\Printer\Printer;

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
}
