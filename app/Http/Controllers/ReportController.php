<?php

namespace App\Http\Controllers;

use App\Exports\ConsumableCountExport;
use App\Exports\ConsumableInstalledCountExport;
use App\Exports\PrintersWorkplaceExport;
use App\Models\Organization;
use App\Services\Query\ConsumableCountInstalledQueryService;
use App\Services\Query\ConsumableCountQueryService;
use App\Services\Query\PrintersWorkplaceQueryService;
use Illuminate\Http\Request;
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
    public function index(): \Inertia\Response
    {
        /** @var \App\Models\Auth\User */
        $user = auth()->user();

        return Inertia::render('Reports/Index', [
            'organizations' => $user->availableOrganizations(),
            'organizationLabels' => Organization::labels(),
        ]);
    }

    /**
     * Формирование отчета по принтерам на местах
     * @route POST /reports/export-printers-workplace
     */
    public function exportPrintersWorkplace(Request $request, PrintersWorkplaceQueryService $printersWorkplaceQueryService): BinaryFileResponse
    {
        $validated = $request->validate([
            'selectedOrganizations' => 'required|array',
            'dateFrom' => 'required_if:withoutPeriod,false',
            'dateTo' => 'required_if:withoutPeriod,false',
            'withoutPeriod' => 'required',
        ], [
            'required' => 'Поле ":attribute" является обязательным для заполнения.',
            'required_if' => 'Поле ":attribute" является обязательным для заполнения, если не выбрано поле ":other".',
        ], [
            'selectedOrganizations' => 'Список организаций',
            'dateFrom' => 'Дата начала',
            'dateTo' => 'Дата окончания',
            'withoutPeriod' => 'Без учета периода',
        ]);

        $withoutPeriod = (bool) ($validated['withoutPeriod'] ?? false);
        $dateFrom = !$withoutPeriod ? ($validated['dateFrom'] ?? null) : null;
        $dateTo = !$withoutPeriod ? ($validated['dateTo'] ?? null) : null;
        $organizations = $validated['selectedOrganizations'];

        return Excel::download(
            new PrintersWorkplaceExport($organizations, $dateFrom, $dateTo, $printersWorkplaceQueryService),
            'printers-workplace.xlsx'
        );
    }

    /**
     * Формирование отчета по остаткам расходных материалов
     * @route POST /reports/export-consumable-count
     */
    public function exportConsumableCount(Request $request, ConsumableCountQueryService $consumableCountQueryService): BinaryFileResponse
    {
        $validated = $request->validate([
            'selectedOrganizations' => 'required|array',
        ], [
            'required' => 'Поле ":attribute" является обязательным для заполнения.',
        ], [
            'selectedOrganizations' => 'Список организаций',
        ]);

        $organizations = $validated['selectedOrganizations'];

        return Excel::download(
            new ConsumableCountExport($organizations, $consumableCountQueryService),
            'consumable-count.xlsx'
        );
    }

    /**
     * Формирование отчета по остаткам картриджей
     * @route POST /reports/export-consumable-installed-count
     */
    public function exportConsumableInstalledCount(Request $request, ConsumableCountInstalledQueryService $queryService): BinaryFileResponse
    {
        $validated = $request->validate([
            'selectedOrganizations' => 'required|array',
            'dateFrom' => 'required_if:withoutPeriod,false',
            'dateTo' => 'required_if:withoutPeriod,false',
            'withoutPeriod' => 'required|boolean',
        ], [
            'required' => 'Поле ":attribute" является обязательным для заполнения.',
            'required_if' => 'Поле ":attribute" является обязательным для заполнения, если не выбрано поле ":other".',
        ], [
            'selectedOrganizations' => 'Список организаций',
            'dateFrom' => 'Дата начала',
            'dateTo' => 'Дата окончания',
            'withoutPeriod' => 'Без учета периода',
        ]);

        $organizations = $validated['selectedOrganizations'];
        $withoutPeriod = (bool) ($validated['withoutPeriod'] ?? false);
        $dateFrom = !$withoutPeriod ? ($validated['dateFrom'] ?? null) : null;
        $dateTo = !$withoutPeriod ? ($validated['dateTo'] ?? null) : null;

        return Excel::download(
            new ConsumableInstalledCountExport($organizations, $dateFrom, $dateTo, $queryService),
            'consumable-installed-count.xlsx'
        );
    }


}
