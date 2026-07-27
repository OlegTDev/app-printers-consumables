<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\Orders\OrderSparePartDetailRequest;
use App\Http\Resources\OrderSparePartResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\OrderSparePartDetails;
use App\Models\Order\OrderSparePartDetailsFile;
use App\Models\Order\Roles;
use App\Services\Order\OrderSparePartDetailUploadFilesService;
use App\Services\Order\OrderStatusButtonService;
use App\Services\Query\OrderQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderSparePartDetailsController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET /orders/spare-parts
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = OrderSparePartDetails::filterByOrgCode()->orderByDesc('id');
        $query->with([
            'sparePart', 'order.requested', 'order.organization',
            'printerWorkplace.printer', 'files',
        ]);

        $paginatedData = $this->getPaginatedData(
            request: $request,
            query: $query,
            filterFields: ['search', 'status', 'organizations'],
            resourceClass: OrderSparePartResource::class,
        );

        return Inertia::render('Orders/SparePart/Index', [
            ...$paginatedData,
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
     * @route GET /orders/spare-parts/create
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Orders/SparePart/Create', [
            'labels' => [
                ...(array)config('labels.order_spare_part'),
                'order' => config('labels.order'),
            ],
        ]);
    }


    /**
     * @route POST /orders/spare-parts
     */
    public function store(OrderSparePartDetailRequest $request, OrderQueryService $orderQueryService): \Illuminate\Http\RedirectResponse
    {
        $modelOrderSparePart = DB::transaction(function () use ($request, $orderQueryService) {
            $modelOrderSparePart = $this->createOrderSparePartDetail($request);

            $orderQueryService->createWithChildOrder(
                subOrder: $modelOrderSparePart,
                authUserOrgCode: auth()->user()->org_code,
                authUserId: auth()->id(),
                comment: $request->input('comment'),
                serviceRequestNumber: $request->input('service_request_number'),
                serviceRequestDate: $request->input('service_request_date'),
                quantity: $request->input('quantity', 1),
            );

            return $modelOrderSparePart;

        });

        $this->uploadFilesIfPresent($modelOrderSparePart, $request);

        return to_route('orders.spare-parts.index')
            ->with('success', 'Заявка успешно добавлена!');
    }

    /**
     * @route GET /orders/spare-parts/{orderSparePartDetails}
     */
    public function show(OrderSparePartDetails $orderSparePartDetails, OrderStatusButtonService $orderStatusButtonService): \Inertia\Response
    {
        $orderSparePartDetails->load(['order.requested', 'files', 'printerWorkplace.printer']);

        $userRoles = auth()->user()->roles()->pluck('name')->toArray();
        $order = $orderSparePartDetails->order;
        $isAuthor = $order->requested_by === auth()->id();
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
     * @route GET /orders/spare-parts/{orderSparePartDetails}/edit
     */
    public function edit(OrderSparePartDetails $orderSparePartDetails): \Inertia\Response
    {
        $this->authorize('update', $orderSparePartDetails->order);

        $orderSparePartDetails->load(['printerWorkplace.printer', 'order']);

        return Inertia::render('Orders/SparePart/Edit', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'labels' => [
                ...(array)config('labels.order_spare_part'),
                'order' => config('labels.order'),
            ],
        ]);
    }

    /**
     * @route PUT /orders/spare-parts/{orderSparePartDetails}
     */
    public function update(OrderSparePartDetailRequest $request, OrderSparePartDetails $orderSparePartDetails): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $orderSparePartDetails->order);

        $validated = $request->safe();

        DB::transaction(function () use ($validated, $orderSparePartDetails) {

            $orderSparePartDetails->update($validated->only(['id_printers_workplace', 'call_specialist', 'id_spare_part']));
            $orderSparePartDetails->order()->update($validated->only(['service_request_number', 'service_request_date', 'comment']));
        });

        return to_route('orders.spare-parts.show', ['orderSparePartDetails' => $orderSparePartDetails])
            ->with('success', 'Изменения сохранены!');
    }

    /**
     * @route GET /orders/spare-parts/{orderSparePartDetails}/edit-files
     */
    public function editFiles(OrderSparePartDetails $orderSparePartDetails): \Inertia\Response
    {
        $this->authorize('update', $orderSparePartDetails->order);

        $orderSparePartDetails->load(['order','files']);

        return Inertia::render('Orders/SparePart/EditFiles', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'labels' => config('labels.order_spare_part'),
        ]);
    }

    /**
     * @route DELETE /orders/spare-parts/{orderSparePartDetails}/files/{orderSparePartDetailsFile}
     */
    public function deleteFile(OrderSparePartDetails $orderSparePartDetails, OrderSparePartDetailsFile $orderSparePartDetailsFile): \Illuminate\Http\RedirectResponse
    {
        if ($orderSparePartDetails->files()->count() == 1) {
            return back()->with('error', 'Невозможно удалить последний файл. Необходимо сначала загрузить файл.');
        }

        $orderSparePartDetailsFile->delete();

        return back()->with('success', 'Файл удален.');
    }

    /**
     * @route POST /orders/spare-parts/{orderSparePartDetails}/files
     */
    public function uploadFiles(OrderSparePartDetails $orderSparePartDetails, Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->uploadFilesIfPresent($orderSparePartDetails, $request);

        return back()->with('success', 'Файл загружен.');
    }

    private function createOrderSparePartDetail(OrderSparePartDetailRequest $request): OrderSparePartDetails
    {
        return new OrderSparePartDetails($request->safe()->only([
            'id_printers_workplace',
            'id_spare_part',
            'call_specialist',
        ]));
    }

    private function uploadFilesIfPresent(OrderSparePartDetails $model, Request $request): void
    {
        if ($request->hasFile('files')) {
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
