<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\Orders\OrderConsumableRequest;
use App\Http\Resources\OrderConsumableResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\OrderConsumableDetails;
use App\Models\Order\Roles;
use App\Services\Order\OrderStatusButtonService;
use App\Services\Query\OrderQueryService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Заказы картриджей
 */
class OrderConsumableDetailsController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET orders/consumables
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = OrderConsumableDetails::filterByOrgCode()->orderBy('id', 'desc');
        $query->with(['consumable.author', 'order.requested', 'order.organization']);

        $paginatedData = $this->getPaginatedData(
            request: $request,
            query: $query,
            filterFields: ['search', 'status', 'organizations'],
            resourceClass: OrderConsumableResource::class,
        );

        return Inertia::render('Orders/Consumable/Index', [
            ...$paginatedData,
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
    public function create(): \Inertia\Response
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
    public function store(OrderConsumableRequest $request, OrderQueryService $orderQueryService): \Illuminate\Http\RedirectResponse
    {
        DB::transaction(function () use ($request, $orderQueryService) {
            $modelConsumable = $this->createOrderConsumable($request);

            $orderQueryService->createWithChildOrder(
                subOrder: $modelConsumable,
                authUserOrgCode: auth()->user()->org_code,
                authUserId: auth()->id(),
                comment: $request->input('comment'),
                serviceRequestNumber: $request->input('service_request_number'),
                serviceRequestDate: $request->input('service_request_date'),
                quantity: $request->input('quantity', 1),
            );
        });

        return to_route('orders.consumables.index')
            ->with('success', 'Заявка успешно добавлена!');
    }

    /**
     * @route GET /orders/consumables/{orderConsumableDetails}
     */
    public function show(OrderConsumableDetails $orderConsumableDetails, OrderStatusButtonService $orderStatusButtonService): \Inertia\Response
    {
        $orderConsumableDetails->load(['order.requested', 'consumable.author']);

        $userRoles = auth()->user()->roles()->pluck('name')->toArray();
        $order = $orderConsumableDetails->order;
        $isAuthor = $order->requested_by === auth()->id();
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
    public function edit(OrderConsumableDetails $orderConsumableDetails): \Inertia\Response
    {
        $orderConsumableDetails->load('order');

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
    public function update(OrderConsumableRequest $request, OrderConsumableDetails $orderConsumableDetails): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $orderConsumableDetails->order);

        $validated = $request->safe();

        DB::transaction(function () use ($orderConsumableDetails, $validated) {
            $orderConsumableDetails->update($validated->only(['id_consumable']));
            $orderConsumableDetails->order()->update($validated->only([
                'quantity',
                'comment',
                'service_request_number',
                'service_request_date',
            ]));
        });
        return to_route('orders.consumables.show', [$orderConsumableDetails->id])
            ->with('success', 'Изменения сохранены!');
    }


    private function createOrderConsumable(OrderConsumableRequest $request): OrderConsumableDetails
    {
        $model = new OrderConsumableDetails($request->safe()->only([
            'id_consumable',
            'quantity',
        ]));
        $model->id_author = auth()->id();
        return $model;
    }

}
