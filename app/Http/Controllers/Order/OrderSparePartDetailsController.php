<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderSparePartDetailRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderSparePartDetails;
use App\Models\Printer\PrinterWorkplace;
use App\Models\SpareParts;
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
    public function index()
    {
        return Inertia::render('Orders/SparePart/Index', [
            'filters' => Request::all(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderSparePartDetailRequest $request)
    {
        $model = new OrderSparePartDetails($request->only(['id_printers_workplace', 'id_spare_part', 'call_specialist']));
        $comment = $request->get('comment');
        Order::newWithChildOrder($model, $comment);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderSparePartDetails $orderSparePartDetails)
    {
        //
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
