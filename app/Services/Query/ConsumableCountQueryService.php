<?php
declare(strict_types=1);

namespace App\Services\Query;

use App\Models\Consumable\ConsumableCount;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

class ConsumableCountQueryService
{
    public function getConsumableCountByPrinterWorkplace(int $printerId, string $orgCode)
    {
        return DB::table('consumables_counts')
            ->select([
                'consumables_counts.id',
                'consumables_counts.id_consumable',
                'consumables_counts.count',
                'consumables.type',
                'consumables.name',
                'consumables.color',
            ])
            ->distinct()
            ->join('consumables', 'consumables.id', '=', 'consumables_counts.id_consumable')
            ->join('consumables_counts_organizations', 'consumables_counts_organizations.id_consumable_count', '=', 'consumables_counts.id')
            ->join('printers_consumables', 'printers_consumables.id_consumable', '=', 'consumables.id')
            ->join('printers', 'printers.id', '=', 'printers_consumables.id_printer')
            ->join('printers_workplace', 'printers_workplace.id_printer', '=', 'printers.id')
            ->where('printers_workplace.org_code', '=', $orgCode)
            ->where('consumables_counts_organizations.org_code', '=', $orgCode)
            ->where('printers.id', '=', $printerId)
            ->get();
    }

    public function buildConsumableCountByOrganizations(array $orgCodes): EloquentBuilder|QueryBuilder
    {
        return ConsumableCount::query()
            ->select([
                DB::raw('ROW_NUMBER() OVER (ORDER BY consumables_counts_organizations.org_code ASC, consumables.name ASC) AS row_num'),
                'consumables_counts.count',
                'consumables_counts_organizations.org_code',
                'consumables.type',
                'consumables.name',
                'consumables.color',
                'consumables.description',
                ])
            ->join('consumables_counts_organizations', 'consumables_counts_organizations.id_consumable_count', '=', 'consumables_counts.id')
            ->join('consumables', 'consumables.id', '=', 'consumables_counts.id_consumable')
            ->whereIn('consumables_counts_organizations.org_code', $orgCodes)
            ->orderBy('consumables_counts_organizations.org_code')
            ->orderBy('consumables.name');
    }
}
