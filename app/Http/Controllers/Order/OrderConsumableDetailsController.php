<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class OrderConsumableDetailsController extends Controller
{
    /**
     * @route GET orders/consumables
     */
    public function index()
    {
        return Inertia::render('Status/WorkInProgress');
    }

}
