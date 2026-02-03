<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dictionary\ConsumableRequest;
use App\Http\Resources\ConsumableResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

/**
 * Управление справочником расходных материалов
 */
class ConsumablesController extends Controller
{
    /**
     * Список расходных материалов
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Dictionary/Consumables/Index', [
            'consumables' => Consumable::filter(Request::only(['search']))->get(),
            'filters' => Request::all(['search']),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
            'labels' => Consumable::labels(),
        ]);
    }

    /**
     * Создание расходного материала
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Dictionary/Consumables/Create', [
            'labels' => Consumable::labels(),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ]);
    }

    /**
     * Сохранение нового расходного материала
     * @param ConsumableRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ConsumableRequest $request)
    {
        $consumable = Consumable::create($request->only(['type', 'name', 'color', 'description']));
        if (!$consumable) {
            return redirect()->back();
        }
        return redirect()->route('dictionary.consumables.index')
            ->with('success', 'Запись успешно добавлена!');
    }

    /**
     * Подробные сведения о расходном материале
     * @param Consumable $consumable расходный материал
     * @return \Inertia\Response
     */
    public function show(Consumable $consumable)
    {
        return Inertia::render('Dictionary/Consumables/Show', [
            'consumable' => [
                'id' => $consumable->id,
                'type' => $consumable->type,
                'name' => $consumable->name,
                'description' => $consumable->description,
                'color' => $consumable->color,
                'author' => $consumable->author,
                'created_at' => $consumable->created_at,
                'updated_at' => $consumable->updated_at,
            ],
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableLabels' => Consumable::labels(),
            'consumableTypeValue' => ConsumableTypesEnum::getValueByName($consumable->type),

            'printersNotIn' => $consumable->printersNotIn()->get(),
            'printers' => $consumable->printers,
            'printerLabels' => Printer::labels(),
        ]);
    }

    /**
     * Редактирование расходного материала
     * @param Consumable $consumable расходный материал
     * @return \Inertia\Response
     */
    public function edit(Consumable $consumable)
    {
        return Inertia::render('Dictionary/Consumables/Edit', [
            'labels' => Consumable::labels(),
            'consumable' => $consumable,
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableTypeValue' => ConsumableTypesEnum::getValueByName($consumable->type),
        ]);
    }

    /**
     * Сохранение измененного расходного материала
     * @param ConsumableRequest $request
     * @param Consumable $consumable расходный материал
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ConsumableRequest $request, Consumable $consumable)
    {
        $consumableUpdate = $consumable->update($request->only(['type', 'name', 'color', 'description']));
        if (!$consumableUpdate) {
            return redirect()->back();
        }
        return redirect()->route('dictionary.consumables.index')
            ->with('success', 'Запись успешно обновлена!');
    }

    /**
     * Удаление расходного материала
     * @param Consumable $consumable расходный материал
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Consumable $consumable)
    {
        $consumable->delete();
        return redirect()->route('dictionary.consumables.index')
            ->with('success', 'Запись успешно удалена!');
    }

    /**
     * @route GET dictionary/consumables/{printer}/other
     */
    public function otherConsumablesForPrinter(Printer $printer)
    {
        $items = Consumable::queryWithOtherTypesByPrinter($printer->id)->get();
        return ConsumableResource::collection($items);
    }

}
