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
use App\Models\SpareParts;
use App\Services\OrderSparePartDetailUploadFilesService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class OrderSparePartDetailsController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:admin,editor-dictionary')
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * @route GET orders/spare-part-details
     */
    public function index(Request $request)
    {
        // $page = $request->input('page',1);
        // $limit = $request->input('limit',10);        

        $orders = OrderSparePartDetails::queryWithFilterByOrgCode()->get();

        return Inertia::render('Orders/SparePart/Index', [
            'filters' => Request::all(['search']),
            'orders' =>  OrderSparePartResource::collection($orders),
            'cartridgeColors' => CartridgeColors::get(),
            'consumableTypes' => ConsumableTypesEnum::array(),
            
            'labels' => [
                'order' => config('labels.order'),
                'spare_parts' => config('labels.spare_parts'),
                'order_spare_part' => config('labels.order_spare_part'),
            ],
        ]);
    }

    
    public function create()
    {
        return Inertia::render('Orders/SparePart/Create', [
            'spareParts' => SpareParts::get()->transform(fn(SpareParts $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
            ]),
            'labels' => OrderSparePartDetails::labels(),
        ]);
    }


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

    
    public function show(OrderSparePartDetails $orderSparePartDetails)
    {        
        return Inertia::render('Orders/SparePart/Show', [
            'orderSparePartDetail' => new OrderSparePartResource($orderSparePartDetails),
            'orderStatusPending' => Order::STATUS_PENDING,
            'orderStatusInProgress' => Order::STATUS_IN_PROGRESS,

            'labels' => [
                'order' => config('labels.order'),
                'spare_parts' => config('labels.spare_parts'),
                'order_spare_part' => config('labels.order_spare_part'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderSparePartDetails $orderSparePartDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderSparePartDetails $orderSparePartDetails)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderSparePartDetails $orderSparePartDetails)
    {
        //
    }
}
