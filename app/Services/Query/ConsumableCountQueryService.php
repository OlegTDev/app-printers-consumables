<?php
declare(strict_types=1);

namespace App\Services\Query;

use Illuminate\Support\Facades\DB;

class ConsumableCountQueryService
{
    public function getConsumableCountByPrinterWorkplace(int $printerWorkplaceId, string $orgCode)
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
            ->where('printers.id', '=', $printerWorkplaceId)
            ->get();
    }
}
