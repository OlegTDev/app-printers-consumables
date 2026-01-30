<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class OrderMistDetailsController extends Controller
{
    /**
     * @route GET orders/misc
     */
    public function index()
    {
        return Inertia::render('Status/WorkInProgress');
    }

}
