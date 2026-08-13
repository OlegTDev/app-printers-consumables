<?php
declare(strict_types=1);

namespace App\Services\Query;

use App\Models\Printer\PrinterWorkplace;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;


class PrintersWorkplaceQueryService
{
    public function buildPrintersWorkplaceInstalledConsumablesByOrganizationsAndPeriod(
        array $organizations,
        ?string $dateFrom,
        ?string $dateTo,
    ): EloquentBuilder|QueryBuilder
    {
        $makeSubquery = function (array $types, bool $isExclude = false) use ($dateFrom, $dateTo) {
            $sub = DB::table("consumables_counts_installed AS cci")
                ->selectRaw("COALESCE(SUM(cci.count), 0)")
                ->join('consumables_counts AS cc_sub', 'cc_sub.id', '=', 'cci.id_consumable_count')
                ->join('consumables AS c_c', 'c_c.id', '=', 'cc_sub.id_consumable')
                ->whereColumn('pw.id', '=', 'cci.id_printer_workplace');

            if (!empty($isExclude)) {
                $sub->whereNotIn('c_c.type', $types);
            } else {
                $sub->whereIn('c_c.type', $types);
            }

            if (!empty($dateFrom)) {
                $sub->where('cci.created_at', '>=', "$dateFrom 00:00:00");
            }
            if (!empty($dateTo)) {
                $sub->where('cci.created_at', '<=', "$dateTo 23:59:59");
            }

            return $sub;
        };

        $subQueryCountCartridge = $makeSubquery(['cartridge']);
        $subQueryCountDrum = $makeSubquery(['drum']);
        $subQueryCountWasteContainer = $makeSubquery(['wasteContainer']);
        $subQueryCountOther = $makeSubquery(['cartridge', 'drum', 'wasteContainer'], true);


        return PrinterWorkplace::query()
            ->from('printers_workplace AS pw')
            ->select([
                DB::raw('ROW_NUMBER() OVER (ORDER BY pw.org_code ASC, pr.vendor ASC, pr.model ASC) as row_num'),
                'pw.id',
                'pw.org_code',
                'pr.vendor',
                'pr.model',
                'pr.is_color_print',
                'pw.location',
                'pw.serial_number',
                'pw.inventory_number',
            ])
            ->selectSub($subQueryCountCartridge, 'count_cartridge')
            ->selectSub($subQueryCountDrum, 'count_drum')
            ->selectSub($subQueryCountWasteContainer, 'count_waste_container')
            ->selectSub($subQueryCountOther, 'count_other')
            ->join('printers AS pr', 'pr.id', '=', 'pw.id_printer')
            ->whereIn('pw.org_code', $organizations)
            ->orderBy('pw.org_code')
            ->orderBy('pr.vendor')
            ->orderBy('pr.model');
    }
}
