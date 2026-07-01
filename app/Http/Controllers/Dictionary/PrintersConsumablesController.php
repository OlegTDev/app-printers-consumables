<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BuildsListQuery;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Привязка принтера к расходным материалам
 */
class PrintersConsumablesController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET /dictionary/printers/{printer}/consumables
     */
    public function index(Printer $printer, Request $request): \Inertia\Response
    {
        $consumables = $this->getPaginatedData(
            request: $request,
            query: $printer->consumablesNotIn(),
        );
        return Inertia::render('Dictionary/Printers/Consumables/Index', [
            ...$consumables,
            'printer' => $printer,
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableLabels' => config('labels.consumable'),
            'cartridgeColors' => CartridgeColors::get(),
        ]);
    }

    /**
     * @route POST /dictionary/printers/{printer}/consumables/{consumable}/add
     */
    public function add(Printer $printer, Consumable $consumable): \Illuminate\Http\RedirectResponse
    {
        $printer->consumables()->attach($consumable->id, ['id_author' => auth()->id()]);
        return to_route('dictionary.printers.show', [$printer])
            ->with('success', 'Связь успешно добавлена!');
    }

    /**
     * @route DELETE /dictionary/printers/{printer}/consumables/{consumable}
     */
    public function destroy(Printer $printer, Consumable $consumable): \Illuminate\Http\RedirectResponse
    {
        $printer->consumables()->detach($consumable->id);
        return to_route('dictionary.printers.show', [$printer])
            ->with('success', 'Связь успешно удалена!');
    }

}
