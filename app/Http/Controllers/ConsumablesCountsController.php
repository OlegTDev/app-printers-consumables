<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsumableCountCorrectRequest;
use App\Http\Requests\ConsumableCountRequest;
use App\Http\Requests\ConsumableCountRequestValidate;
use App\Http\Resources\ConsumableCountResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use App\Services\Consumables\ConsumableCountService;
use App\Services\Query\ConsumableCountQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Redirect;

class ConsumablesCountsController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin,add-consumables')->only(['store']);
        $this->middleware('role:admin,subtract-consumable')->only(['update']);
    }

    /**
     * Поиск всех расходных материалов (для списка)
     * @use ConsumablesCountsController::index()
     * @return \Illuminate\Support\Collection
     */
    private function allConsumables()
    {
        return Consumable::all()->transform(fn(Consumable $consumable) => [
            'id' => $consumable->id,
            'name' => ConsumableTypesEnum::getValueByName($consumable->type) . ' ' . $consumable->name
                . ($consumable->type === 'cartridge' ? ' (' . (CartridgeColors::get()[$consumable->color]['name'] ?? $consumable->color) . ')' : ''),
        ]);
    }

    /**
     * @route GET /consumables/counts
     */
    public function index(): \Inertia\Response
    {
        $consumablesCounts = ConsumableCount::filter(Request::only(['search', 'consumableType']))->forCurrentUser()->get();
        return Inertia::render('Consumable/Count/Index', [
            'filters' => Request::all(['search', 'consumableType']),
            'consumablesCounts' => $consumablesCounts,
            'consumableLabels' => config('labels.consumable'),
            'consumableCountLabels' => ConsumableCount::labels(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
        ]);
    }

    /**
     * Список расходных материалов связанных с принтером $printer
     * и текущей организацией
     * @param Printer $printer
     * @return array
     */
    public function listByPrinter(Printer $printer, ConsumableCountQueryService $consumableCountQueryService)
    {
        return
        [
            'consumables' => $consumableCountQueryService->getConsumableCountByPrinterWorkplace($printer->id, Auth::user()->org_code),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'cartridgeColors' => CartridgeColors::get(),
        ];
    }

    /**
     * Форма создания записи ConsumableCount
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Consumable/Count/Create', [
            'consumableLabels' => config('labels.consumable'),
            'consumableCountLabels' => ConsumableCount::labels(),
            'consumables' => $this->allConsumables(),
            'availableOrganizations' => Auth::user()->availableOrganizations(),
        ]);
    }

    /**
     * @route POST /consumables/counts
     */
    public function store(ConsumableCountRequest $request, ConsumableCountService $consumablesCountsAddService)
    {
        $idConsumable = $request->integer('id_consumable');
        $organizations = $request->collect('selectedOrganizations')->toArray();
        $count = $request->integer('count');
        $changeOrganization = $request->boolean('changeOrganization');

        $idConsumableCount = $consumablesCountsAddService->add($idConsumable, $changeOrganization, $organizations, $count);

        return redirect()->route('consumables.counts.show', [$idConsumableCount])
            ->with('success', 'Данные успешно сохранены!');
    }

    /**
     * @route GET /consumables/counts/{idConsumable}/exists
     */
    public function exists(int $idConsumable)
    {
        $consumableCount = ConsumableCount::where('id_consumable', $idConsumable)
            ->forCurrentUser()->firstOrFail();

        return new ConsumableCountResource($consumableCount);
    }

    /**
     * Валидация данных при добавлении нового документа ConsumableCount
     * @param ConsumableCountRequestValidate $request
     */
    public function validateConsumableCount(ConsumableCountRequestValidate $request)
    {
        abort(599, 'Удалить контроллер?');
    }

    /**
     * Отображение информации о количестве по расходному материалу $consumableCount
     *
     * @return \Inertia\Response
     */
    public function show(ConsumableCount $count)
    {
        $consumableCount = $count;
        if (!in_array(Auth::user()->org_code, $consumableCount->organizationsCodes()->toArray())) {
            abort(404);
        }

        $consumable = $consumableCount->consumable;
        return Inertia::render('Consumable/Count/Show', [
            'consumableCount' => $consumableCount,
            'consumable' => $consumable,
            'consumableTitle' => $consumable->title(),
            'consumableCountLabels' => ConsumableCount::labels(),
            'organizations' => $consumableCount->organizations,
            'organizationLabels' => Organization::labels(),
            'allOrganizations' => Organization::all(),
        ]);
    }

    /**
     * Прибавление количества (поступление расходных материалов)
     * @param ConsumableCountRequest|mixed $request
     * @param ConsumableCount $count общее количество
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ConsumableCountRequest $request, ConsumableCount $count)
    {
        DB::beginTransaction();

        // создание модели ConsumableCountAdded с добавляемым количеством count
        $consumableCountAdded = new ConsumableCountAdded([
            'id_consumable_count' => $count->id,
            'count' => $request->get('count'),
        ]);

        // результаты выполнения
        if (!$consumableCountAdded->save()) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Возникла ошибка при сохранении!');
        }

        DB::commit();

        return redirect()->back()
            ->with('success', 'Данные успешно сохранены!');

    }

    /**
     * @route POST /consumables/counts/{count}/correct
     */
    public function correctCount(ConsumableCountCorrectRequest $request, ConsumableCount $count): RedirectResponse
    {
        $count->count = $request->input('count', 0);
        $count->save();

        return redirect()->back()
            ->with('success', 'Данные успешно сохранены!');
    }

    /**
     * @route POST /consumables/counts/{count}/organizations
     */
    public function updateOrganizations(ConsumableCountRequest $request, ConsumableCount $count): RedirectResponse
    {
        $organizations = $request->collect('selectedOrganizations')->toArray();
        $count->organizations()->sync($organizations);
        return redirect()->route('consumables.counts.show', [$count])
            ->with('success', 'Данные успешно сохранены!');
    }

}
