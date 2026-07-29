<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\ConsumableCountCorrectRequest;
use App\Http\Requests\ConsumableCountRequest;
use App\Http\Resources\ConsumableCountResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Services\Consumables\ConsumableCountService;
use App\Services\Query\OrganizationQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ConsumablesCountsController extends Controller
{
    use BuildsListQuery;

    private function allConsumables(): \Illuminate\Support\Collection
    {
        return Consumable::select(['id', 'name', 'type', 'color'])
            ->get()
            ->map(fn(Consumable $consumable) => [
                'id' => $consumable->id,
                'name' => $consumable->title(),
        ]);
    }

    /**
     * @route GET /consumables/counts
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = ConsumableCount::forCurrentUser();

        $paginatedData = $this->getPaginatedData(
            request: $request,
            query: $query,
            filterFields: ['search', 'consumableType'],
            resourceClass: ConsumableCountResource::class,
        );

        return Inertia::render('Consumable/Count/Index', [
            ...$paginatedData,
            'consumableLabels' => config('labels.consumable'),
            'consumableCountLabels' => config('labels.consumable_count'),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
        ]);
    }

    /**
     * @route GET /consumables/counts/create
     */
    public function create(OrganizationQueryService $organizationQueryService): \Inertia\Response
    {
        $availableOrganizations = auth()->user()->availableOrganizations();

        return Inertia::render('Consumable/Count/Create', [
            'consumableLabels' => config('labels.consumable'),
            'consumableCountLabels' => config('labels.consumable_count'),
            'consumables' => $this->allConsumables(),
            'availableOrganizations' => $organizationQueryService->getOrganizationsTree($availableOrganizations),
        ]);
    }

    /**
     * @route POST /consumables/counts
     */
    public function store(ConsumableCountRequest $request, ConsumableCountService $consumablesCountsService): RedirectResponse
    {
        $validated = $request->safe();

        $idConsumableCount = $consumablesCountsService->add(
            idConsumable: $validated->integer('id_consumable'),
            count: $validated->integer('count'),
            idUser: auth()->id(),
            findOrgCode: auth()->user()->org_code,
            organizations: $validated->array('selectedOrganizations'),
        );

        return to_route('consumables.counts.show', [$idConsumableCount])
            ->with('success', 'Данные успешно сохранены!');
    }

    /**
     * @route GET /consumables/counts/{count}
     */
    public function show(int $id): \Inertia\Response
    {
        $consumableCount = ConsumableCount::query()
            ->forCurrentUser()
            ->with(['consumable', 'organizations'])
            ->where('id', $id)
            ->firstOrFail();

        Gate::authorize('show', $consumableCount);

        $organizations = Organization::select(['code', 'name'])->get();

        return Inertia::render('Consumable/Count/Show', [
            'consumableCount' => ConsumableCountResource::make($consumableCount),
            'consumableCountLabels' => config('labels.consumable_count'),
            'organizationLabels' => config('labels.organization'),
            'organizations' => OrganizationResource::collection($organizations),
        ]);
    }

    /**
     * @route PUT /consumables/counts/{count}
     */
    public function update(
        ConsumableCountRequest $request,
        ConsumableCount $count,
        ConsumableCountService $consumablesCountsAddService,
    ): RedirectResponse
    {
        $consumablesCountsAddService->update(
            idConsumableCount: $count->id,
            count: $request->safe()->integer('count'),
            idUser: auth()->id(),
        );

        return back()->with('success', 'Данные успешно сохранены!');
    }

    /**
     * @route POST /consumables/counts/{count}/correct
     */
    public function correctCount(
        ConsumableCountCorrectRequest $request,
        ConsumableCount $count,
        ConsumableCountService $consumableCountService,
    ): RedirectResponse
    {
        $consumableCountService->correctBalance($count, $request->safe()->input('count'));

        return back()->with('success', 'Данные успешно сохранены!');
    }

    /**
     * @route POST /consumables/counts/{count}/organizations
     */
    public function updateOrganizations(ConsumableCountRequest $request, ConsumableCount $count): RedirectResponse
    {
        $organizations = $request->safe()->collect('selectedOrganizations')->toArray();

        $count->organizations()->sync($organizations);

        return to_route('consumables.counts.show', [$count])
            ->with('success', 'Данные успешно сохранены!');
    }

}
