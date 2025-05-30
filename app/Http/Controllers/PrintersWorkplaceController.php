<?php

namespace App\Http\Controllers;

use App\Http\Requests\PrinterWorkplaceRequest;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

/**
 * Принтеры на рабочих местах
 * 
 */
class PrintersWorkplaceController extends Controller
{

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        // настройка прав доступа
        $this->middleware('role:admin,editor-printer-workplace')
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
        $this->middleware('role:admin,subtract-consumable')
            ->only(['list']);
    }


    /**
     * Все принтеры (из справочника)
     * Для использования в выпадающем списке (dropdown)
     * @return \Illuminate\Support\Collection
     */
    private function allPrinters()
    {
        return Printer::orderBy('vendor')
            ->orderBy('model')
            ->get()
            ->transform(fn(Printer $printer) => [
                'id' => $printer->id,
                'name' => "$printer->vendor $printer->model",
            ]);
    }

    /**
     * Список принтеров на рабочих местах,
     * привязанных к организации, которая установлена у пользователя
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Printers/Index', [
            'filters' => Request::all(['search']),
            'printersWorkplace' => PrinterWorkplace::filter(Request::only(['search']))->get(),
            'printerWorkplaceLabels' => PrinterWorkplace::labels(),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ]);
    }

    /**
     * Список принтеров на рабочих местах для выпадающего списка
     * привязанных к расходному материалу $consumable и к текущей организации
     * @route GET /printers/workplace/list/{consumable}
     * @param Consumable $consumable
     * @return \Illuminate\Support\Collection
     */
    public function list(Consumable $consumable)
    {
        return $consumable->printersWorkplace();
    }

    /**
     * Список всех принтеров на рабочих местах для выпадающего списка
     * привязанных к текущей организации
     * @route GET /printers/workplace/all
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return PrinterWorkplace::with('printer')->where('org_code', Auth::user()->org_code)->get();
    }

    /**
     * Добавление принтера
     * @route GET /printers/workplace/create
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Printers/Create', [
            'labels' => PrinterWorkplace::labels(),
            'printers' => $this->allPrinters(),
            'organizations' => Auth::user()->availableOrganizations(),
        ]);
    }

    /**
     * Сохранение нового принтера
     * @route POST /printers/workplace
     * @param PrinterWorkplaceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PrinterWorkplaceRequest $request)
    {
        /** @var \Illuminate\Http\Request $request для исключения ошибки в IDE */
        $printerWorkplace = PrinterWorkplace::create($request->only(['id_printer', 'location', 'serial_number', 'inventory_number', 'org_code']));
        if (!$printerWorkplace) {
            return redirect()->back();
        }
        return redirect()->route('workplace.index')
            ->with('success', 'Запись успешно добавлена!');
    }

    /**
     * Детальная информация о принтере 
     * @route GET /printers/workplace/{workplace}
     * @param PrinterWorkplace $workplace
     * @return \Inertia\Response
     */
    public function show(PrinterWorkplace $workplace)
    {
        return Inertia::render('Printers/Show/Show', [
            'printerWorkplace' => $workplace,
            'printerWorkplace.printer' => $workplace->printer,
            'printerWorkplace.author' => $workplace->author,
            'printerLabels' => Printer::labels(),
            'printerWorkplaceLabels' => PrinterWorkplace::labels(),
            'organization' => $workplace->organization,

            'consumables' => $workplace->printer->consumablesDeep,
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableLabels' => Consumable::labels(),
            'consumableCountLabels' => ConsumableCount::labels(),
        ]);
    }

    /**
     * Редактирование принтера 
     * @route GET /printers/workplace/{workplace}/edit
     * @param PrinterWorkplace $workplace
     * @return \Inertia\Response
     */
    public function edit(PrinterWorkplace $workplace)
    {
        return Inertia::render('Printers/Edit', [
            'printerWorkplace' => $workplace,
            'printerWorkplace.printer' => $workplace->printer,
            'printers' => $this->allPrinters(),
            'labels' => PrinterWorkplace::labels(),
            'organizations' => Auth::user()->availableOrganizations(),
        ]);
    }

    /**
     * Сохранение отредактированного принтера 
     * @route PUT /printers/workplace/{workplace}
     * @param PrinterWorkplace $workplace
     * @param PrinterWorkplaceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PrinterWorkplaceRequest $request, PrinterWorkplace $workplace): RedirectResponse
    {
        /** @var \Illuminate\Http\Request $request для исключения ошибки в IDE */
        $workplaceUpdate = $workplace->update($request->only(['id_printer', 'location', 'serial_number', 'inventory_number', 'org_code']));
        if (!$workplaceUpdate) {
            return redirect()->back();
        }
        return redirect()->route('workplace.show', $workplace)
            ->with('success', 'Запись успешно обновлена!');
    }

    /**
     * Удаление принтера 
     * @route DELETE /printers/workplace/{workplace}
     * @param PrinterWorkplace $workplace
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PrinterWorkplace $workplace): RedirectResponse
    {
        $workplace->delete();
        return redirect()->route('workplace.index')
            ->with('success', 'Запись успешно удалена!');
    }

    /**
     * Установленные расходные материалы для принтера $workplace
     * @route GET /printers/workplace/consumables-installed/{workplace}
     * @param \App\Models\Printer\PrinterWorkplace $workplace
     * @return \Illuminate\Support\Collection
     */
    public function consumablesInstalled(PrinterWorkplace $workplace): Collection
    {
        return $workplace->consumablesInstalled();
    }
}
