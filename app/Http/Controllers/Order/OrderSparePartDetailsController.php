<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderSparePartDetailRequest;
use App\Http\Resources\OrderSparePartResource;
use App\Http\Resources\SparePartsResource;
use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Order\Order;
use App\Models\Order\OrderSparePartDetails;
use App\Models\Order\OrderSparePartDetailsFile;
use App\Models\SpareParts;
use App\Services\OrderSparePartDetailUploadFilesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class OrderSparePartDetailsController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin,order-approver')->only(['create', 'store', 'cancel']);
        $this->middleware('role:admin')->only(['edit', 'update', 'destroy']);
    }

    /**
     * @route GET orders/spare-parts
     */
    public function index(Request $request)
    {
        $orders = OrderSparePartDetails::queryWithFilterByOrgCode()            
            ->filter($request->only(['search', 'status', 'organizations']))->get();

        return Inertia::render('Orders/SparePart/Index', [
            'filters' => $request->all(['search', 'status', 'organizations']),
            'orders' =>  OrderSparePartResource::collection($orders),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            'statuses' => Order::statusLabels(),
            
            'labels' => [
                'order' => config('labels.order'),
                'spare_parts' => config('labels.spare_parts'),
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
            'spareParts' => SparePartsResource::collection(SpareParts::get()),           
            'labels' => array_merge([
                'order' => config('labels.order'),
            ], config('labels.order_spare_part')),
        ]);
    }


    /**
     * @route POST orders/spare-parts
     */
    public function store(OrderSparePartDetailRequest $request)
    {
        DB::transaction(function () use ($request) {
            $model = new OrderSparePartDetails($request->only(['id_printers_workplace', 'id_spare_part', 'call_specialist']));
            $comment = $request->get('comment');
            Order::createWithChildOrder($model, $comment);

            if ($request->has('files')) {
                $uploadedPaths = (new OrderSparePartDetailUploadFilesService($request->file('files')))->upload();
                foreach ($uploadedPaths as $uploadedPath) {
                    OrderSparePartDetailsFile::create([
                        'id_spare_part_order_detail' => $model->id,
                        'filename' => $uploadedPath,
                    ]);
                }
            }
        });

        return redirect()->route('spare-parts.index')
            ->with('success', 'Заявка успешно добавлена!');
    }

    /**
     * @route GET orders/spare-parts/{orderSparePartDetails}
     */
    public function show(OrderSparePartDetails $orderSparePartDetails)
    {        
        return Inertia::render('Orders/SparePart/Show', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'orderStatusPending' => Order::STATUS_PENDING,
            'orderStatusInProgress' => Order::STATUS_IN_PROGRESS,
            'orderStatusCancelled' => Order::STATUS_CANCELLED,
            'isAuthor' => $orderSparePartDetails->order->requested_by === auth()->user()->id,

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
        return Inertia::render('Orders/SparePart/Edit', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'spareParts' => SparePartsResource::collection(SpareParts::get()),
            'labels' => array_merge([
                'order' => config('labels.order'),
            ], config('labels.order_spare_part')),
        ]);
    }

    /**
     * @route PUT orders/spare-parts/{orderSparePartDetails}
     */
    public function update(OrderSparePartDetailRequest $request, OrderSparePartDetails $orderSparePartDetails)
    {
        $orderSparePartDetails->update($request->only(['id_printers_workplace', 'call_specialist', 'id_spare_part']));
        return redirect()->route('spare-parts.show', ['orderSparePartDetails' => $orderSparePartDetails])
            ->with('success', 'Изменения сохранены!');
    }

    /**
     * @route DELETE orders/spare-parts/{orderSparePartDetails}/delete-file/{orderSparePartDetailsFile}
     */
    public function deleteFile(OrderSparePartDetails $orderSparePartDetails, OrderSparePartDetailsFile $orderSparePartDetailsFile)
    {
        if ($orderSparePartDetails->files()->count() == 1) {            
            return Redirect::back()->with('error', 'Необходимо сначала загрузить файл.');
        }
    
        $orderSparePartDetailsFile->delete();

        return Redirect::back()->with('success', 'Файл удален.');
    }

    /**
     * @route POST orders/spare-parts/{orderSparePartDetails}/upload-files
     */
    public function uploadFiles(OrderSparePartDetails $orderSparePartDetails, Request $request)
    {
        if ($request->has('files')) {
            $uploadedPaths = (new OrderSparePartDetailUploadFilesService($request->file('files')))->upload();
            foreach ($uploadedPaths as $uploadedPath) {
                OrderSparePartDetailsFile::create([
                    'id_spare_part_order_detail' => $orderSparePartDetails->id,
                    'filename' => $uploadedPath,
                ]);
            }
        }
        return Redirect::back()->with('success', 'Файл загружен.');
    }

    

}
