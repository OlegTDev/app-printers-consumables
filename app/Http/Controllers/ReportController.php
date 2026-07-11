<?php

namespace App\Http\Controllers;

use App\Exports\ConsumableCountExport;
use App\Exports\ConsumableInstalledCountExport;
use App\Exports\PrintersWorkplaceExport;
use App\Http\Requests\ReportBaseRequest;
use App\Http\Requests\ReportWithPeriodRequest;
use App\Services\Query\ConsumableCountInstalledQueryService;
use App\Services\Query\ConsumableCountQueryService;
use App\Services\Query\OrganizationQueryService;
use App\Services\Query\PrintersWorkplaceQueryService;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Отчеты
 */
final class ReportController extends Controller
{

    /**
     * @route GET /reports
     */
    public function index(OrganizationQueryService $organizationQueryService): \Inertia\Response
    {

        $availableOrganizations = auth()->user()->availableOrganizations();

        return Inertia::render('Reports/Index', [
            'organizations' => $organizationQueryService->getOrganizationsTree($availableOrganizations),
            'organizationLabels' => config('labels.organization'),
        ]);
    }

    /**
     * Формирование отчета по принтерам на местах
     * @route POST /reports/export-printers-workplace
     */
    public function exportPrintersWorkplace(ReportWithPeriodRequest $request, PrintersWorkplaceQueryService $printersWorkplaceQueryService): BinaryFileResponse
    {
        $validated = $request->safe();

        $withoutPeriod = $validated->boolean('withoutPeriod');
        $dateFrom = !$withoutPeriod ? $validated->string('dateFrom') : null;
        $dateTo = !$withoutPeriod ? $validated->string('dateTo') : null;
        $organizations = $validated->array('selectedOrganizations');

        return Excel::download(
            new PrintersWorkplaceExport($organizations, $dateFrom, $dateTo, $printersWorkplaceQueryService),
            'printers-workplace.xlsx'
        );
    }

    /**
     * Формирование отчета по остаткам расходных материалов
     * @route POST /reports/export-consumable-count
     */
    public function exportConsumableCount(ReportBaseRequest $request, ConsumableCountQueryService $consumableCountQueryService): BinaryFileResponse
    {
        $validated = $request->safe();

        $organizations = $validated->array('selectedOrganizations');

        return Excel::download(
            new ConsumableCountExport($organizations, $consumableCountQueryService),
            'consumable-count.xlsx'
        );
    }

    /**
     * Формирование отчета по установленным картриджам
     * @route POST /reports/export-consumable-installed-count
     */
    public function exportConsumableInstalledCount(ReportWithPeriodRequest $request, ConsumableCountInstalledQueryService $queryService): BinaryFileResponse
    {
        $validated = $request->safe();

        $withoutPeriod = $validated->boolean('withoutPeriod');
        $dateFrom = !$withoutPeriod ? $validated->string('dateFrom') : null;
        $dateTo = !$withoutPeriod ? $validated->string('dateTo') : null;
        $organizations = $validated->array('selectedOrganizations');

        return Excel::download(
            new ConsumableInstalledCountExport($organizations, $dateFrom, $dateTo, $queryService),
            'consumable-installed-count.xlsx'
        );
    }


}
