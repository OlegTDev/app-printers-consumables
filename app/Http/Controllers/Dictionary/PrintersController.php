<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\Dictionary\PrinterRequest;
use App\Http\Resources\PrinterResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Manufacturer;
use App\Models\Printer\Printer;
use App\Services\Query\ManufacturerQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Управление справочником принтеров
 */
class PrintersController extends Controller
{
    use BuildsListQuery;

    public function __construct()
    {
        $this->middleware('role:admin,editor-dictionary')
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * @route GET /dictionary/printers
     */
    public function index(Request $request): \Inertia\Response
    {
        $params = $this->getPaginatedData(
            request: $request,
            query: Printer::query(),
            allowSortFields: ['id', 'vendor', 'model', 'created_at'],
        );

        return Inertia::render('Dictionary/Printers/Index', $params);
    }

    /**
     * @route GET /dictionary/printers/create
     */
    public function create(ManufacturerQueryService $manufacturerQueryService): \Inertia\Response
    {
        $manufacturers = $manufacturerQueryService->getAll()->transform(fn(Manufacturer $item) => [
            'label' => $item->name,
            'value' => $item->name,
        ]);

        return Inertia::render('Dictionary/Printers/Create', [
            'labels' => config('labels.printer'),
            'manufacturers' => $manufacturers,
        ]);
    }

    /**
     * @route POST /dictionary/printers
     */
    public function store(PrinterRequest $request, ): \Illuminate\Http\RedirectResponse
    {
        Printer::create($request->validated());

        return to_route('dictionary.printers.index')
            ->with('success', 'Запись успешно добавлена!');
    }

    /**
     * @route GET /dictionary/printers/{printer}
     */
    public function show(Printer $printer): \Inertia\Response
    {
        return Inertia::render('Dictionary/Printers/Show', [
            'printer' => new PrinterResource($printer),
            'printerLabels' => config('labels.printer'),

            'consumables' => $printer->consumables,
            'consumablesNotIn' => $printer->consumablesNotIn()->get(),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableLabels' => config('labels.consumable'),
        ]);
    }

    /**
     * @route GET /dictionary/printers/{printer}/edit
     */
    public function edit(Printer $printer, ManufacturerQueryService $manufacturerQueryService)
    {
        $manufacturers = $manufacturerQueryService->getAll()->transform(fn(Manufacturer $item) => [
            'label' => $item->name,
            'value' => $item->name,
        ]);
        return Inertia::render('Dictionary/Printers/Edit', [
            'printer' => new PrinterResource($printer),
            'labels' => config('labels.printer'),
            'manufacturers' => $manufacturers,
        ]);
    }

    /**
     * @route PUT /dictionary/printers/{printer}
     */
    public function update(PrinterRequest $request, Printer $printer)
    {
        $printer->update($request->validated());

        return to_route('dictionary.printers.index')
            ->with('success', 'Запись успешно обновлена!');
    }

    /**
     * @route DELETE /dictionary/printers/{printer}
     */
    public function destroy(Printer $printer)
    {
        $printer->delete();

        return to_route('dictionary.printers.index')
            ->with('success', 'Запись успешно удалена!');
    }
}
