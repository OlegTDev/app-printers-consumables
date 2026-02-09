<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderSparePartDetailRequest;
use App\Http\Resources\OrderSparePartResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderSparePartDetails;
use App\Models\Order\OrderSparePartDetailsFile;
use App\Models\Order\Roles;
use App\Services\OrderSparePartDetailUploadFilesService;
use App\Services\OrderStatusButtonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class OrderSparePartDetailsController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin')
            ->only(['destroy']);
    }

    /**
     * @route GET orders/spare-parts
     */
    public function index(Request $request)
    {
        $orders = OrderSparePartDetails::queryWithFilterByOrgCode()
            ->orderByDesc('id')
            ->filter($request->only(['search', 'status', 'organizations']))->get();

        return Inertia::render('Orders/SparePart/Index', [
            'filters' => $request->all(['search', 'status', 'organizations']),
            'orders' => OrderSparePartResource::collection($orders),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'statuses' => config('order_statuses'),


            'labels' => [
                'order' => config('labels.order'),
                'order_spare_part' => config('labels.order_spare_part'),
            ],
        ]);
    }

    /**
     * @route GET orders/spare-parts/create
     */
    public function create()
    {
        return Inertia::render('Orders/SparePart/Create', [
            'labels' => [
                ...(array)config('labels.order_spare_part'),
                'order' => config('labels.order'),
            ],
        ]);
    }


    /**
     * @route POST orders/spare-parts
     */
    public function store(OrderSparePartDetailRequest $request)
    {
        DB::transaction(function () use ($request) {
            $modelOrderSparePart = $this->createOrderSparePartDetail($request);
            $this->createChildOrder($modelOrderSparePart,
                $request->input('comment'),
                $request->input('service_request_number'),
                $request->input('service_request_date'),
            );
            $this->uploadFiles($modelOrderSparePart, $request);
        });

        return redirect()->route('spare-parts.index')
            ->with('success', 'Заявка успешно добавлена!');
    }

    /**
     * @route GET orders/spare-parts/{orderSparePartDetails}
     */
    public function show(OrderSparePartDetails $orderSparePartDetails, OrderStatusButtonService $orderStatusButtonService)
    {
        $userRoles = auth()->user()->getRoleNames();
        $order = $orderSparePartDetails->order;
        $isAuthor = $order->requested_by === auth()->user()->id;
        if ($isAuthor) {
            $userRoles[] = Roles::ORDER_AUTHOR->value;
        }
        $buttons = $orderStatusButtonService->getAvailableButtons($order->status, $userRoles);

        return Inertia::render('Orders/SparePart/Show', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'statuses' => config('order_statuses'),
            'isAuthor' => $isAuthor,
            'buttons' => $buttons,
            'labels' => [
                'order' => config('labels.order'),
                'spare_parts' => config('labels.spare_parts'),
                'order_spare_part' => config('labels.order_spare_part'),
            ],
        ]);
    }

    /**
     * @route GET orders/spare-parts/{orderSparePartDetails}/edit
     */
    public function edit(OrderSparePartDetails $orderSparePartDetails)
    {
        $this->authorize('update', $orderSparePartDetails->order);

        return Inertia::render('Orders/SparePart/Edit', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'labels' => [
                ...(array)config('labels.order_spare_part'),
                'order' => config('labels.order'),
            ],
        ]);
    }

    /**
     * @route PUT orders/spare-parts/{orderSparePartDetails}
     */
    public function update(OrderSparePartDetailRequest $request, OrderSparePartDetails $orderSparePartDetails)
    {
        $this->authorize('update', $orderSparePartDetails->order);

        $orderSparePartDetails->update($request->only(['id_printers_workplace', 'call_specialist', 'id_spare_part']));
        $orderSparePartDetails->order()->update($request->only(['service_request_number', 'service_request_date']));
        return redirect()->route('spare-parts.show', ['orderSparePartDetails' => $orderSparePartDetails])
            ->with('success', 'Изменения сохранены!');
    }

    /**
     * @route GET orders/spare-parts/{orderSparePartDetails}/edit-files
     */
    public function editFiles(OrderSparePartDetails $orderSparePartDetails)
    {
        $this->authorize('update', $orderSparePartDetails->order);
        return Inertia::render('Orders/SparePart/EditFiles', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'labels' => config('labels.order_spare_part'),
        ]);
    }

    /**
     * @route DELETE orders/spare-parts/{orderSparePartDetails}/files/{orderSparePartDetailsFile}
     */
    public function deleteFile(OrderSparePartDetails $orderSparePartDetails, OrderSparePartDetailsFile $orderSparePartDetailsFile)
    {
        if ($orderSparePartDetails->files()->count() == 1) {
            return Redirect::back()->with('error', 'Невозможно удалить последний файл. Необходимо сначала загрузить файл.');
        }

        $orderSparePartDetailsFile->delete();

        return Redirect::back()->with('success', 'Файл удален.');
    }

    /**
     * @route POST orders/spare-parts/{orderSparePartDetails}/files
     */
    public function uploadFiles(OrderSparePartDetails $orderSparePartDetails, Request $request)
    {
        $this->uploadFilesIfPresent($orderSparePartDetails, $request);
        return Redirect::back()->with('success', 'Файл загружен.');
    }


    private function createOrderSparePartDetail(Request $request): OrderSparePartDetails
    {
        return new OrderSparePartDetails($request->only([
            'id_printers_workplace',
            'id_spare_part',
            'call_specialist',
        ]));
    }

    private function createChildOrder(OrderSparePartDetails $orderSparePartDetails,
        ?string $comment, ?string $service_request_number, ?string $service_request_date): void
    {
        Order::createWithChildOrder($orderSparePartDetails, $comment, $service_request_number, $service_request_date);
    }

    private function uploadFilesIfPresent(OrderSparePartDetails $model, Request $request): void
    {
        if ($request->has('files')) {
            $uploadedPaths = (new OrderSparePartDetailUploadFilesService($request->file('files')))->upload();
            foreach ($uploadedPaths as $uploadedPath) {
                OrderSparePartDetailsFile::create([
                    'id_spare_part_order_detail' => $model->id,
                    'filename' => $uploadedPath,
                ]);
            }
        }
    }

}
