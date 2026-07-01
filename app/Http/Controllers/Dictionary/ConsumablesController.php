<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\Dictionary\ConsumableRequest;
use App\Http\Resources\ConsumableResource;
use App\Http\Resources\PrinterResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Управление справочником расходных материалов
 */
class ConsumablesController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET /dictionary/consumables
     */
    public function index(Request $request): \Inertia\Response
    {
        $params = $this->getPaginatedData(
            request: $request,
            query: Consumable::with('author'),
            allowSortFields: ['id', 'type', 'name', 'created_at']
        );

        return Inertia::render('Dictionary/Consumables/Index', [
            ...$params,
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
            'labels' => config('labels.consumable'),
        ]);
    }

    /**
     * @route GET /dictionary/consumables/create
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Dictionary/Consumables/Create', [
            'labels' => config('labels.consumable'),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ]);
    }

    /**
     * @route POST /dictionary/consumables
     */
    public function store(ConsumableRequest $request): \Illuminate\Http\RedirectResponse
    {
        Consumable::create($request->validated());
        return to_route('dictionary.consumables.index')
            ->with('success', 'Запись успешно добавлена!');
    }

    /**
     * @route GET /dictionary/consumables/{consumable}
     */
    public function show(Consumable $consumable): \Inertia\Response
    {
        $consumable->load(['author', 'printers']);
        return Inertia::render('Dictionary/Consumables/Show', [
            'consumable' => new ConsumableResource($consumable),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableLabels' => config('labels.consumable'),
            'consumableTypeValue' => ConsumableTypesEnum::getValueByName($consumable->type),

            'printersNotIn' => PrinterResource::collection($consumable->printersNotIn()->get()),
            'printers' => PrinterResource::collection($consumable->printers),
            'printerLabels' => config('labels.printer'),
        ]);
    }

    /**
     * @route GET /dictionary/consumables/{consumable}/edit
     */
    public function edit(Consumable $consumable): \Inertia\Response
    {
        return Inertia::render('Dictionary/Consumables/Edit', [
            'labels' => config('labels.consumable'),
            'consumable' => new ConsumableResource($consumable),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableTypeValue' => ConsumableTypesEnum::getValueByName($consumable->type),
        ]);
    }

    /**
     * @route PUT /dictionary/consumables/{consumable}
     */
    public function update(ConsumableRequest $request, Consumable $consumable): \Illuminate\Http\RedirectResponse
    {
        $consumable->update($request->validated());
        return to_route('dictionary.consumables.index')
            ->with('success', 'Запись успешно обновлена!');
    }

    /**
     * @route DELETE /dictionary/consumables/{consumable}
     */
    public function destroy(Consumable $consumable): \Illuminate\Http\RedirectResponse
    {
        $consumable->delete();
        return to_route('dictionary.consumables.index')
            ->with('success', 'Запись успешно удалена!');
    }

}
