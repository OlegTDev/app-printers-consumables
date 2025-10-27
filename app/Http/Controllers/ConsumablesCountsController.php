<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsumableCountCorrectRequest;
use App\Http\Requests\ConsumableCountRequest;
use App\Http\Requests\ConsumableCountRequestValidate;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableCount;
use App\Models\Consumable\ConsumableCountAdded;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Organization;
use App\Models\Printer\Printer;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
     * Список записей о количестве расходных материалов
     * @return \Inertia\Response
     */
    public function index()
    {        
        $consumablesCounts = ConsumableCount::filter(Request::only(['search', 'consumableType']))->get();
        return Inertia::render('Consumable/Count/Index', [
            'filters' => Request::all(['search', 'consumableType']),
            'consumablesCounts' => $consumablesCounts,
            'consumableLabels' => Consumable::labels(),    
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
    public function listByPrinter(Printer $printer)
    {
        return
        [
            'data' => DB::table('consumables_counts')                
                ->select([
                    'consumables_counts.id',
                    'consumables_counts.id_consumable',
                    'consumables_counts.count',
                    'consumables.type',
                    'consumables.name',
                    'consumables.color',
                ])->distinct()                
                    ->join('consumables', 'consumables.id', '=', 'consumables_counts.id_consumable')
                    ->join('consumables_counts_organizations', 'consumables_counts_organizations.id_consumable_count', '=', 'consumables_counts.id')
                    ->join('printers_consumables', 'printers_consumables.id_consumable', '=', 'consumables.id')
                    ->join('printers', 'printers.id', '=', 'printers_consumables.id_printer')
                    ->join('printers_workplace', 'printers_workplace.id_printer', '=', 'printers.id')
                ->where('printers_workplace.org_code', '=', Auth::user()->org_code)
                ->where('consumables_counts_organizations.org_code', '=', Auth::user()->org_code)
                ->where('printers.id', '=', $printer->id)
                ->get(),
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
            'consumableLabels' => Consumable::labels(),
            'consumableCountLabels' => ConsumableCount::labels(),
            'consumables' => $this->allConsumables(),
            'availableOrganizations' => Auth::user()->availableOrganizations(),
        ]);
    }

    /**
     * Сохранение (добавление/изменение) ConsumableCount
     * 
     * По выбранному идентификатору расходного материала (id_consumable) и текущей организации Auth::user()->org_code выполняется поиск
     * Если запись найдена, то для добавления используется она, в противном случае создается новая запись
     * 
     * В случае, если запись найдена и указано о необходимости обновления списка организаций (changeOrganization == true),
     * то список организаций будет заменен на selectedOrganizations
     * @param ConsumableCountRequest|mixed $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ConsumableCountRequest $request)
    {
        // получение данных из формы запроса
        $organizations = $request->get('selectedOrganizations');
        $idConsumable = $request->get('id_consumable');
        $count = $request->get('count');
        $changeOrganization = $request->get('changeOrganization', false);

        DB::beginTransaction();
        $isNew = false;

        // поиск или создание записи о количестве расходных материалов
        // поиск выполняется по id_consumable и наличию текущей организации Auth::user()->org_code
        $consumableCount = ConsumableCount::findByIdConsumable($idConsumable);
        // если запись не найдена, то создается новая
        if (!$consumableCount) {
            $isNew = true;
            $consumableCount = new ConsumableCount([
                'id_consumable' => $idConsumable,
                'count' => 0,
            ]);
            $consumableCount->save();           
        }
        
        // изменение списка организаций
        // если новая запись или если было указано, что необходимо изменить список организаций
        if ($isNew || $changeOrganization) {
            $consumableCount->updateOrganizations($organizations);
        }  

        // создание модели ConsumableCountAdded с добавляемым количеством count
        $consumableCountAdded = new ConsumableCountAdded([
            'id_consumable_count' => $consumableCount->id,
            'count' => $count,
        ]);

        // результаты выполнения
        if (!$consumableCountAdded->save()) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Возникла ошибка при сохранении!');
        }
        DB::commit();
        return redirect()->route('counts.show', [$consumableCount])
            ->with('success', 'Данные успешно сохранены!');
    }

    /**
     * Поиск записи ConsumableCount с переданным id_consumable
     * @return array|null
     */
    public function checkExists()
    {
        $idConsumable = Request::get('id_consumable');
        if ($idConsumable === null) {
            throw new \Exception('Attribute id_consumable is null');
        }

        $consumableCount = ConsumableCount::findByIdConsumable($idConsumable);        
        if ($consumableCount !== null) {
            return [
                'id' => $consumableCount->id,
                'count' => $consumableCount->count,
                'organizations' => $consumableCount->organizationsCodes(),
            ];
        }
    }    

    /**
     * Валидация данных при добавлении нового документа ConsumableCount
     * @param ConsumableCountRequestValidate $request    
     */
    public function validateConsumableCount(ConsumableCountRequestValidate $request) { }

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
        
        return redirect()->route('counts.show', [$count])
            ->with('success', 'Данные успешно сохранены!');

    }

    /**
     * Корректировка количества
     * @param \App\Http\Requests\ConsumableCountCorrectRequest|mixed $request
     * @param \App\Models\Consumable\ConsumableCount $count
     * @return \Illuminate\Http\RedirectResponse
     */
    public function correctCount(ConsumableCountCorrectRequest $request, ConsumableCount $count)
    {
        $count->count = $request->get('count', 0);
        $count->save();

        return redirect()->route('counts.show', [$count])
            ->with('success', 'Данные успешно сохранены!');
    }

    /**
     * Изменение списка привязанных организаций
     * @param ConsumableCountRequest|mixed $request
     * @param ConsumableCount $count общее количество
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateOrganizations(ConsumableCountRequest $request, ConsumableCount $count)
    {
        $organizations = $request->get('selectedOrganizations');
        $count->updateOrganizations($organizations);
        return redirect()->route('counts.show', [$count])
            ->with('success', 'Данные успешно сохранены!');
    }

}
