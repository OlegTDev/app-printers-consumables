<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConsumableCountAddedResource;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Services\Consumables\ConsumableCountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class ConsumablesCountsAddedController extends Controller
{

    /**
     * @route GET /consumables/{consumable}/counts/{count}/added
     */
    public function index(Consumable $consumable, ConsumableCount $count): JsonResource
    {
        $count->load(['consumablesAdded.author']);
        return ConsumableCountAddedResource::collection($count->consumablesAdded);
    }

    /**
     * @route DELETE /consumables/{consumable}/counts/{count}/added/{added}
     */
    public function destroy(
        Consumable $consumable,
        ConsumableCount $count,
        ConsumableCountAdded $added,
        ConsumableCountService $consumableCountService,
    ): RedirectResponse
    {
        Gate::authorize('delete', $added);

        $consumableCountService->removeConsumableCountAdded($added);

        return back()->with('success', 'Запись удалена');
    }
}
