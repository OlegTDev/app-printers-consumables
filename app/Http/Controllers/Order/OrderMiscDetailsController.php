<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderMiscRequest;
use App\Http\Resources\OrderMiscResource;
use App\Models\Order\Order;
use App\Models\Order\OrderMiscDetails;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderMiscDetailsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')
            ->only(['destroy']);
    }

    /**
     * @route GET orders/misc
     */
    public function index(Request $request)
    {
        $orders = OrderMiscDetails::queryWithFilterByOrgCode()
            ->filter($request->only(['search', 'status', 'organizations']))
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Orders/Misc/Index', [
            'filters' => $request->all(['search', 'status', 'organizations']),
            'orders' => OrderMiscResource::collection($orders),
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
    public function create()
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
    public function store(OrderMiscRequest $request)
    {
        $modelOrderMisc = $this->createOrderMisc($request);
        $this->createChildOrder(
            orderMisc: $modelOrderMisc,
            quantity: $request->input('quantity', 1),
            comment: $request->input('comment'),
        );

        return redirect()->route('misc.index')
            ->with('success', 'Заявка успешно добавлена!');
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

    private function createChildOrder(OrderMiscDetails $orderMisc,
        int $quantity, ?string $comment, ?string $service_request_number = null, ?string $service_request_date = null): void
    {
        Order::createWithChildOrder(
            subOrder: $orderMisc,
            comment: $comment,
            service_request_number: $service_request_number,
            service_request_date: $service_request_date,
            quantity: $quantity,
        );
    }



}
