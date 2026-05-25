<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsumableCountInstalledRequest;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Consumable\ConsumableTypesEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ConsumablesCountsInstalledController extends Controller
{

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->middleware('role:admin,subtract-consumable')->only(['store']);
        $this->middleware('role:admin')->only('destroy');
    }

    /**
     * Список количества добавленных расходных материалов
     * @param Consumable $consumable расходный материал
     * @param ConsumableCount $count общее количество
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(Consumable $consumable, ConsumableCount $count)
    {
        return $count->consumablesInstalled()->with('author')->get();
    }

    /**
     * Последние установленные расходные материалы
     * @return array
     */
    public function last($limit = 30)
    {
        return [
            'data' => ConsumableCountInstalled::with([
                'consumableCount' => fn($query) => $query->with('consumable'),
                'printerWorkplace' => fn($query) => $query->with('printer'),
                'author',
            ])
                ->whereHas('printerWorkplace', fn($query) => $query->where('org_code', Auth::user()->org_code))
                ->limit($limit)
                ->orderByDesc('created_at')->get(),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ];
    }

    /**
     * Сохранение установленного расходного материала
     * @param Consumable $consumable
     * @param ConsumableCount $count
     * @param ConsumableCountInstalledRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Consumable $consumable, ConsumableCount $count, ConsumableCountInstalledRequest $request)
    {
        $consumableCountInstalled = new ConsumableCountInstalled($request->only(['id_consumable_count', 'id_printer_workplace', 'count']));
        if (!$consumableCountInstalled->save()) {
            return redirect()->back()->with('error', 'Возникла ошибка при сохранении!');
        }
        return redirect()->back()->with('success', 'Данные успешно сохранены!');
    }

    /**
     * @route /consumables/{consumable}/counts/{count}/installed/{installed}
     */
    public function destroy(Consumable $consumable, ConsumableCount $count, ConsumableCountInstalled $installed): RedirectResponse
    {
        if (Auth::user()->hasRole('admin') || $installed->id_author !== Auth::user()->id) {
            $installed->delete();
            return redirect()->back()
                ->with('success', 'Запись удалена');
        }
        throw new AuthorizationException();
    }

}
