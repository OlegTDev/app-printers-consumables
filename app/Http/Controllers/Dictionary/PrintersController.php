<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dictionary\PrinterRequest;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Manufacturer;
use App\Models\Printer\Printer;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

/**
 * Управление справочником принтеров
 */
class PrintersController extends Controller
{

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        // настройка прав доступа
        $this->middleware('role:admin,editor-dictionary')
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    private function manufacturersList()
    {
        return Manufacturer::query()->get()->transform(fn(Manufacturer $item) => [
            'label' => $item->name,
            'value' => $item->name,
        ]);
    }

    /**
     * Список принтеров
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Dictionary/Printers/Index', [
            'printers' => Printer::filter(Request::only(['search']))->get(),
            'filters' => Request::all(['search']),
        ]);
    }

    /**
     * Добавление принтера
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Dictionary/Printers/Create', [
            'labels' => Printer::labels(),
            'manufacturers' => $this->manufacturersList(),
        ]);
    }

    /**
     * Сохранение нового принтера
     * @param PrinterRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PrinterRequest $request)
    {
        $printer = Printer::create($request->only(['vendor', 'model', 'color_print']));
        if (!$printer) {
            return redirect()->back();
        }
        return redirect()->route('dictionary.printers.index')
            ->with('success', 'Запись успешно добавлена!');
    }

    /**
     * Детальная информация о принтере $printer
     * @param Printer $printer
     * @return \Inertia\Response
     */
    public function show(Printer $printer)
    {
        return Inertia::render('Dictionary/Printers/Show', [
            'printer' => [
                'id' => $printer->id,
                'vendor' => $printer->vendor,
                'model' => $printer->model,
                'is_color_print' => $printer->is_color_print,
                'author' => $printer->author,
                'created_at' => $printer->created_at,
                'updated_at' => $printer->updated_at,
            ],
            'printerLabels' => Printer::labels(),

            'consumables' => $printer->consumables,
            'consumablesNotIn' => $printer->consumablesNotIn()->get(),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableLabels' => config('labels.consumable'),
        ]);
    }

    /**
     * Редактирование принтера $printer
     * @param Printer $printer
     * @return \Inertia\Response
     */
    public function edit(Printer $printer)
    {
        return Inertia::render('Dictionary/Printers/Edit', [
            'printer' => $printer->toArray(),
            'labels' => Printer::labels(),
            'manufacturers' => $this->manufacturersList(),
        ]);
    }

    /**
     * Сохранение отредактированного принтера $printer
     * @param Printer $printer
     * @param PrinterRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PrinterRequest $request, Printer $printer)
    {
        $printerUpdate = $printer->update($request->only(['vendor', 'model', 'is_color_print']));
        if (!$printerUpdate) {
            return redirect()->back();
        }
        return redirect()->route('dictionary.printers.index')
            ->with('success', 'Запись успешно обновлена!');
    }

    /**
     * Удаление принтера $printer
     * @param Printer $printer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Printer $printer)
    {
        $printer->delete();
        return redirect()->route('dictionary.printers.index')
            ->with('success', 'Запись успешно удалена!');
    }
}
