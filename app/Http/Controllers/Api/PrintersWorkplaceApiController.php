<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\PrinterWorkplaceResource;
use App\Models\Consumable\Consumable;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintersWorkplaceApiController
{
    /**
     * @route GET /printers/workplace/list/{consumable}
     */
    public function listByConsumable(Consumable $consumable): JsonResource
    {
        $workplaces = $consumable->printersWorkplaces()
            ->with('printer')
            ->where('printers_workplace.org_code', auth()->user()->org_code)
            ->get();
        return PrinterWorkplaceResource::collection($workplaces);
    }

    /**
     * @route GET /printers/workplace/all
     */
    public function all(): JsonResource
    {
        //return PrinterWorkplace::with('printer')->where('org_code', Auth::user()->org_code)->get();
        $workplaces = PrinterWorkplace::with('printer')
            ->where('org_code', auth()->user()->org_code)
            ->get();
        return PrinterWorkplaceResource::collection($workplaces);
    }
}
