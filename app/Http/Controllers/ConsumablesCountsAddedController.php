<?php

namespace App\Http\Controllers;

use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ConsumablesCountsAddedController extends Controller
{

    /**
     * Список количества добавленных расходных материалов
     * @param Consumable $consumable расходный материал
     * @param ConsumableCount $count общее количество
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(Consumable $consumable, ConsumableCount $count)
    {
        return $count->consumablesAdded()->with('author')->get();
    }

    /**
     * @route DELETE /consumables/{consumable}/counts/{count}/added/{added}
     */
    public function destroy(Consumable $consumable, ConsumableCount $count, ConsumableCountAdded $added): RedirectResponse
    {
        $this->middleware('role:admins')->only(['destroy']);
        if (Auth::user()->hasRole('admin') || $added->id_author !== Auth::user()->id) {
            $added->delete();
            return redirect()->back()
                ->with('success', 'Запись удалена');
        }
        throw new AuthorizationException();
    }
}
