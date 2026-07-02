<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Resources\PrinterResource;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Привязка расходного материала к принтерам
 */
class ConsumablesPrintersController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET /dictionary/consumables/{consumable}/printers
     */
    public function index(Consumable $consumable, Request $request): \Inertia\Response
    {
        $params = $this->getPaginatedData(
            request: $request,
            query: $consumable->printersNotIn(),
            allowSortFields: ['id', 'name', 'created_at'],
            resourceClass: PrinterResource::class,
        );
        return Inertia::render('Dictionary/Consumables/Printers/Index', [
            ...$params,
            'consumable' => $consumable,
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableTypeValue' => ConsumableTypesEnum::getValueByName($consumable->type),
        ]);
    }

    /**
     * @route POST /dictionary/consumables/{consumable}/printers/{printer}
     */
    public function store(Consumable $consumable, Printer $printer): \Illuminate\Http\RedirectResponse
    {
        $consumable->printers()->syncWithoutDetaching([$printer->id => ['id_author' => auth()->id()]]);
        return to_route('dictionary.consumables.show', [$consumable->id])
            ->with('success', 'Связь успешно добавлена!');
    }

    /**
     * @route DELETE /dictionary/consumables/{consumable}/printers/{printer}
     */
    public function destroy(Consumable $consumable, Printer $printer): \Illuminate\Http\RedirectResponse
    {
        $consumable->printers()->detach($printer->id);
        return to_route('dictionary.consumables.show', [$consumable->id])
            ->with('success', 'Связь успешно удалена!');
    }
}
