<?php

namespace App\Http\Controllers\Order;

use App\Http\Requests\Orders\OrderConsumableRequest;
use App\Http\Resources\OrderConsumableResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderConsumableDetails;
use App\Models\Order\Roles;
use App\Services\OrderStatusButtonService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;


class OrderConsumableDetailsController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin')
            ->only(['destroy']);
    }

    /**
     * @route GET orders/consumables
     */
    public function index(Request $request)
    {
        $orders = OrderConsumableDetails::queryWithFilterByOrgCode()
            ->orderBy('id', 'desc')
            ->get();
            //->fil
        return Inertia::render('Orders/Consumable/Index', [
            'filters' => $request->all(['search', 'status', 'organizations']),
            'orders' => OrderConsumableResource::collection($orders),
            'statuses' => config('order_statuses'),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),

            'labels' => [
                'order' => config('labels.order'),
                'order_consumable' => config('labels.order_consumable'),
            ],
        ]);
    }

    /**
     * @route GET orders/consumables/create
     */
    public function create()
    {
        return Inertia::render('Orders/Consumable/Create', [
            'labels' => [
                ...(array)config('labels.order_consumable'),
                'order' => config('labels.order'),
            ],
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ]);
    }

    /**
     * @route POST orders/consumables
     */
    public function store(OrderConsumableRequest $request)
    {
        $modelConsumable = $this->createOrderConsumable($request);
        $this->createChildOrder($modelConsumable,
            $request->input('comment'),
            $request->input('service_request_number'),
            $request->input('service_request_date'),
        );

        return redirect()->route('consumables.index')
            ->with('success', 'Заявка успешно добавлена!');
    }

    /**
     * @route GET /orders/consumables/{orderConsumableDetails}
     */
    public function show(OrderConsumableDetails $orderConsumableDetails, OrderStatusButtonService $orderStatusButtonService)
    {
        $userRoles = auth()->user()->getRoleNames();
        $order = $orderConsumableDetails->order;
        $isAuthor = $order->requested_by === auth()->user()->id;
        if ($isAuthor) {
            $userRoles[] = Roles::ORDER_AUTHOR->value;
        }
        $buttons = $orderStatusButtonService->getAvailableButtons($order->status, $userRoles);


        return Inertia::render('Orders/Consumable/Show', [
            'orderConsumableDetail' => new OrderConsumableResource($orderConsumableDetails),
            'statuses' => config('order_statuses'),
            'isAuthor' => $isAuthor,
            'buttons' => $buttons,
            'labels' => [
                'order' => config('labels.order'),
                'order_consumable' => config('labels.order_consumable'),
            ],
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ]);
    }

    /**
     * @route GET /orders/consumables/{orderConsumableDetails}/edit
     */
    public function edit(OrderConsumableDetails $orderConsumableDetails)
    {
        $this->authorize('update', $orderConsumableDetails->order);

        return Inertia::render('Orders/Consumable/Edit', [
            'orderConsumableDetail' => new OrderConsumableResource($orderConsumableDetails),
            'labels' => [
                ...(array)config('labels.order_consumable'),
                'order' => config('labels.order'),
            ],
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
        ]);
    }

    /**
     * @route PUT orders/consumables/{orderConsumableDetails}
     */
    public function update(OrderConsumableRequest $request, OrderConsumableDetails $orderConsumableDetails)
    {
        $this->authorize('update', $orderConsumableDetails->order);

        $orderConsumableDetails->update($request->only(['id_consumable', 'quantity']));
        $orderConsumableDetails->order()->update($request->only(['comment', 'service_request_number', 'service_request_date']));
        return redirect()->route('consumables.show', ['orderConsumableDetails' => $orderConsumableDetails])
            ->with('success', 'Изменения сохранены!');
    }


    private function createOrderConsumable(Request $request): OrderConsumableDetails
    {
        $model = new OrderConsumableDetails($request->only([
            'id_consumable',
            'quantity',
        ]));
        $model->id_author = auth()->id();
        return $model;
    }

    private function createChildOrder(OrderConsumableDetails $orderConsumable,
        ?string $comment, ?string $service_request_number, ?string $service_request_date): void
    {
        Order::createWithChildOrder($orderConsumable, $comment, $service_request_number, $service_request_date);
    }

}
