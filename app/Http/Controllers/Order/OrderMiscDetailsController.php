<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\Orders\OrderMiscRequest;
use App\Http\Resources\OrderMiscResource;
use App\Models\Order\OrderMiscDetails;
use App\Models\Order\Roles;
use App\Services\Order\OrderStatusButtonService;
use App\Services\Query\OrderQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderMiscDetailsController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET orders/misc
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = OrderMiscDetails::filterByOrgCode()->orderByDesc('id');
        $query->with(['order.requested', 'order.organization']);

        $paginatedData = $this->getPaginatedData(
            request: $request,
            query: $query,
            filterFields: ['search', 'status', 'organizations'],
            resourceClass: OrderMiscResource::class,
        );

        return Inertia::render('Orders/Misc/Index', [
            ...$paginatedData,
            'statuses' => config('order_statuses'),
            'labels' => [
                'order' => config('labels.order'),
                'order_misc' => config('labels.order_misc'),
            ],
        ]);
    }

    /**
     * @route GET orders/misc/create
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Orders/Misc/Create', [
            'labels' => [
                ...(array)config('labels.order_misc'),
                'order' => config('labels.order'),
            ],
        ]);
    }

    /**
     * @route POST orders/misc
     */
    public function store(OrderMiscRequest $request, OrderQueryService $orderQueryService): \Illuminate\Http\RedirectResponse
    {
        DB::transaction(function () use ($request, $orderQueryService) {
            $modelOrderMisc = $this->createOrderMisc($request);

            $orderQueryService->createWithChildOrder(
                subOrder: $modelOrderMisc,
                authUserOrgCode: auth()->user()->org_code,
                authUserId: auth()->id(),
                comment: $request->input('comment'),
                quantity: $request->input('quantity', 1),
            );
        });

        return to_route('orders.misc.index')
            ->with('success', 'Заявка успешно добавлена!');
    }

    /**
     * @route GET orders/misc/{orderMiscDetails}
     */
    public function show(OrderMiscDetails $orderMiscDetails, OrderStatusButtonService $orderStatusButtonService): \Inertia\Response
    {
        $orderMiscDetails->load(['order.requested']);

        $userRoles = auth()->user()->getRoleNames();
        $order = $orderMiscDetails->order;
        $isAuthor = $order->requested_by === auth()->user()->id;
        if ($isAuthor) {
            $userRoles[] = Roles::ORDER_AUTHOR->value;
        }
        $buttons = $orderStatusButtonService->getAvailableButtons($order->status, $userRoles);

        return Inertia::render('Orders/Misc/Show', [
            'orderMiscDetail' => new OrderMiscResource($orderMiscDetails),
            'statuses' => config('order_statuses'),
            'isAuthor' => $isAuthor,
            'buttons' => $buttons,
            'labels' => [
                ...(array)config('labels.order_misc'),
                'order' => config('labels.order'),
            ],
        ]);
    }

    /**
     * @route GET orders/misc/{orderMiscDetails}/edit
     */
    public function edit(OrderMiscDetails $orderMiscDetails): \Inertia\Response
    {
        $orderMiscDetails->load(['order']);

        $this->authorize('update', $orderMiscDetails->order);

        return Inertia::render('Orders/Misc/Edit', [
            'orderMiscDetail' => new OrderMiscResource($orderMiscDetails),
            'labels' => [
                ...(array)config('labels.order_misc'),
                'order' => config('labels.order'),
            ],
        ]);
    }

    /**
     * @route PUT orders/misc/{orderMiscDetails}
     */
    public function update(OrderMiscRequest $request, OrderMiscDetails $orderMiscDetails): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $orderMiscDetails->order);

        $validated = $request->safe();

        DB::transaction(function () use ($orderMiscDetails, $validated) {
            $orderMiscDetails->update($validated->only(['name', 'description']));
            $orderMiscDetails->order()->update($validated->only(['comment']));
        });
        return to_route('orders.misc.show', ['orderMiscDetails' => $orderMiscDetails->id])
            ->with('success', 'Изменения сохранены!');
    }


    private function createOrderMisc(Request $request): OrderMiscDetails
    {
        $model = new OrderMiscDetails($request->only([
            'name',
            'description',
        ]));
        $model->id_author = auth()->id();
        return $model;
    }

}
