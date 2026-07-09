<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\PrinterWorkplaceRequest;
use App\Http\Resources\PrinterResource;
use App\Http\Resources\PrinterWorkplaceResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Принтеры на рабочих местах
 */
class PrintersWorkplaceController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET /printers/workplace
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = PrinterWorkplace::with(['printer.consumables.consumableCountCurrentOrganization'])
            ->forCurrentUser()
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at');

        $dataPaginated = $this->getPaginatedData(
            request: $request,
            query: $query,
            resourceClass: PrinterWorkplaceResource::class,
        );

        return Inertia::render('Printers/Index', [
            ...$dataPaginated,
            'printerWorkplaceLabels' => config('labels.printer_workplace'),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ]);
    }


    /**
     * @route GET /printers/workplace/create
     */
    public function create(): \Inertia\Response
    {
        $printers = Printer::orderBy('vendor')
            ->orderBy('model')
            ->get();

        return Inertia::render('Printers/Create', [
            'labels' => config('labels.printer_workplace'),
            'printers' => PrinterResource::collection($printers),
            'organizations' => auth()->user()->availableOrganizations(),
        ]);
    }

    /**
     * @route POST /printers/workplace
     */
    public function store(PrinterWorkplaceRequest $request)
    {
        PrinterWorkplace::create($request->validated());

        return to_route('workplace.index')
            ->with('success', 'Запись успешно добавлена!');
    }

    /**
     * @route GET /printers/workplace/{workplace}
     */
    public function show(PrinterWorkplace $workplace)
    {
        $workplace->load(['printer.consumables.consumableCountCurrentOrganization', 'author', 'organization']);

        return Inertia::render('Printers/Show/Show', [
            'printerWorkplace' => PrinterWorkplaceResource::make($workplace),
            'printerLabels' => config('labels.printer'),
            'printerWorkplaceLabels' => config('labels.printer_workplace'),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableLabels' => config('labels.consumable'),
            'consumableCountLabels' => ConsumableCount::labels(),
        ]);
    }

    /**
     * @route GET /printers/workplace/{workplace}/edit
     */
    public function edit(PrinterWorkplace $workplace): \Inertia\Response
    {
        $printers = Printer::orderBy('vendor')
            ->orderBy('model')
            ->get();

        $workplace->load(['printer']);

        return Inertia::render('Printers/Edit', [
            'printerWorkplace' => PrinterWorkplaceResource::make($workplace),
            'printers' => PrinterResource::collection($printers),
            'labels' => config('labels.printer_workplace'),
            'organizations' => Auth::user()->availableOrganizations(),
        ]);
    }

    /**
     * @route PUT /printers/workplace/{workplace}
     */
    public function update(PrinterWorkplaceRequest $request, PrinterWorkplace $workplace): RedirectResponse
    {
        $workplace->update($request->validated());

        return to_route('workplace.show', $workplace)
            ->with('success', 'Запись успешно обновлена!');
    }

    /**
     * @route DELETE /printers/workplace/{workplace}
     */
    public function destroy(PrinterWorkplace $workplace): RedirectResponse
    {
        $workplace->delete();

        return to_route('workplace.index')
            ->with('success', 'Запись успешно удалена!');
    }

}
