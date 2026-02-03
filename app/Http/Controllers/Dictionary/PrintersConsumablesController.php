<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\Printer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

/**
 * Привязка принтера к расходным материалам
 */
class PrintersConsumablesController extends Controller
{
    /**
     * Список расходных материалов привязанных к принтеру $printer
     * @param Printer $printer
     * @return \Inertia\Response
     */
    public function index(Printer $printer)
    {
        return Inertia::render('Dictionary/Printers/Consumables/Index', [
            'consumables' => $printer->consumablesNotIn()->filter(Request::only(['search']))->get(),
            'filters' => Request::all(['search']),
            'printer' => $printer,
            'consumableTypes' => ConsumableTypesEnum::array(),
            'consumableLabels' => config('labels.consumable'),
            'cartridgeColors' => CartridgeColors::get(),
        ]);
    }

    /**
     * Привязка расходного материала $consumable с принтером $printer
     * @param Consumable $consumable
     * @param Printer $printer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(Printer $printer, Consumable $consumable)
    {
        $printer->consumables()->attach($consumable->id, ['id_author' => Auth::id()]);
        return redirect()->route('dictionary.printers.show', [$printer])
            ->with('success', 'Связь успешно добавлена!');
    }

    /**
     * Удаление привязки расходного материала $consumable с принтером $printer
     * @param Consumable $consumable
     * @param Printer $printer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Printer $printer, Consumable $consumable)
    {
        $printer->consumables()->detach($consumable->id);
        return redirect()->route('dictionary.printers.show', [$printer])
            ->with('success', 'Связь успешно удалена!');
    }

}
