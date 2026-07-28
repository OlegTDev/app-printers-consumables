<?php

namespace App\Http\Controllers;

use App\Services\Query\ChartQueryService;

/**
 * Графики на главной странице
 */
class ChartController extends Controller
{

    /**
     * @route GET /chart/last-added
     */
    public function lastAdded(ChartQueryService $queryService, int $limit = 30)
    {
        return $queryService->getLastConsumablesAdded(auth()->user()->org_code, $limit);
    }

    /**
     * @route GET /chart/last-installed
     */
    public function lastInstalled(ChartQueryService $queryService, int $limit = 30)
    {
        return $queryService->getLastConsumablesInstalled(auth()->user()->org_code, $limit);
    }

}
