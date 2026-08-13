<?php
declare(strict_types=1);

namespace App\Services\Query;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Models\Consumable\Consumable;
use Illuminate\Support\Facades\DB;

class ConsumableCountInstalledQueryService
{
    public function buildCountInstalled(array $organizations, ?string $dateFrom, ?string $dateTo): QueryBuilder|EloquentBuilder
    {
        $installedSubQuery = DB::table('consumables_counts_installed AS cci')
            ->select([
                'cci.id_consumable_count',
                'pw.org_code',
                DB::raw('COALESCE(SUM(cci.count), 0) AS total_installed'),
            ])
            ->join('printers_workplace AS pw', 'pw.id', '=', 'cci.id_printer_workplace')
            ->groupBy('cci.id_consumable_count', 'pw.org_code');

        if (!empty($dateFrom)) {
            $installedSubQuery->where('cci.created_at', '>=', "$dateFrom 00:00:00");
        }
        if (!empty($dateTo)) {
            $installedSubQuery->where('cci.created_at', '<=', "$dateTo 23:59:59");
        }

        $orgMappedSubQuery = DB::table('consumables_counts AS cc')
            ->select([
                'cc.id_consumable',
                'cc.count AS count_now',
                'cc.id AS count_id',
                DB::raw("STRING_AGG(DISTINCT cco.org_code, ', ' ORDER BY cco.org_code) AS org_codes"),
                DB::raw('COALESCE(SUM(ia.total_installed), 0) AS count_installed')
            ])
            ->join('consumables_counts_organizations AS cco', 'cc.id', '=', 'cco.id_consumable_count')
            ->leftJoinSub($installedSubQuery, 'ia', function($join) {
                $join->on('ia.id_consumable_count', '=', 'cc.id')
                     ->on('ia.org_code', '=', 'cco.org_code');
            })
            ->whereIn('cco.org_code', $organizations)
            ->groupBy('cc.id_consumable', 'cc.count', 'cc.id');

        return Consumable::query()
            ->from('consumables AS cons')
            ->select([
                DB::raw("ROW_NUMBER() OVER (ORDER BY COALESCE(om.org_codes, '') ASC, cons.type ASC, cons.name ASC) as row_num"),
                'cons.id',
                'cons.type',
                'cons.name',
                'cons.color',
                'cons.description',
                DB::raw("COALESCE(om.org_codes, '') AS org_code"),
                DB::raw('COALESCE(om.count_installed, 0) AS count_installed'),
                DB::raw('COALESCE(om.count_now, 0) AS count_now'),
                'om.count_id',
            ])
            ->joinSub($orgMappedSubQuery, 'om', 'om.id_consumable', '=', 'cons.id')
            ->orderBy(DB::raw("COALESCE(om.org_codes, '')"))
            ->orderBy('cons.type')
            ->orderBy('cons.name');
    }

}
