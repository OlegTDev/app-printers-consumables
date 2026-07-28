<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsumableCountInstalledRequest;
use App\Http\Resources\ConsumableCountInstalledResource;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Services\Consumables\ConsumableCountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class ConsumablesCountsInstalledController extends Controller
{

    /**
     * @route GET /consumables/{consumable}/counts/{count}/installed
     */
    public function index(Consumable $consumable, ConsumableCount $count): JsonResource
    {
        $count->load('consumablesInstalled.author');
        return ConsumableCountInstalledResource::collection($count->consumablesInstalled);
    }

    /**
     * @route POST /consumables/{consumable}/counts/{count}/installed
     */
    public function store(
        Consumable $consumable,
        ConsumableCount $count,
        ConsumableCountInstalledRequest $request,
        ConsumableCountService $consumableCountService,
    ): RedirectResponse
    {
        Gate::authorize('store', ConsumableCountInstalled::class);

        $validated = $request->safe();

        $consumableCountService->install(
            idConsumableCount: $count->id,
            idPrinterWorkplace: $validated->integer('id_printer_workplace'),
            count: $validated->integer('count'),
            idUser: auth()->id(),
        );

        return back()->with('success', 'Данные успешно сохранены!');
    }

    /**
     * @route /consumables/{consumable}/counts/{count}/installed/{installed}
     */
    public function destroy(
        Consumable $consumable,
        ConsumableCount $count,
        ConsumableCountInstalled $installed,
        ConsumableCountService $consumableCountService,
    ): RedirectResponse
    {
        Gate::authorize('delete', $installed);

        $consumableCountService->removeConsumableCountInstalled($installed);

        return back()->with('success', 'Запись удалена');
    }

}
