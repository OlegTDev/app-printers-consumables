<?php
declare(strict_types=1);

namespace App\Services\Query;

use Illuminate\Support\Facades\DB;

class ChartQueryService
{
    public function getLastConsumablesAdded(string $orgCode, int $limit = 30): array
    {
        $query = <<<SQL
        SELECT
            date("consumables_counts_added"."created_at") AS "date",
            SUM("consumables_counts_added"."count") AS "count"
        FROM "consumables_counts_added"
        INNER JOIN "consumables_counts" ON "consumables_counts"."id" = "consumables_counts_added"."id_consumable_count"
        INNER JOIN "consumables_counts_organizations" ON "consumables_counts_organizations"."id_consumable_count" = "consumables_counts"."id"
        WHERE "consumables_counts_organizations"."org_code" = :org_code
        GROUP BY date("consumables_counts_added"."created_at")
        ORDER BY date("consumables_counts_added"."created_at") DESC
        LIMIT :limit
        SQL;

        return DB::select($query, [
            ':org_code' => $orgCode,
            ':limit' => $limit,
        ]);
    }

    public function getLastConsumablesInstalled(string $orgCode, int $limit = 30): array
    {
        $query = <<<SQL
        SELECT
            date("consumables_counts_installed"."created_at") AS "date",
            SUM("consumables_counts_installed"."count") AS "count"
        FROM "consumables_counts_installed"
        INNER JOIN "consumables_counts" ON "consumables_counts"."id" = "consumables_counts_installed"."id_consumable_count"
        INNER JOIN "printers_consumables" ON "printers_consumables"."id_consumable" = "consumables_counts"."id_consumable"
        INNER JOIN "printers_workplace" ON "printers_workplace"."id_printer" = "printers_consumables"."id_printer"
        WHERE "printers_workplace"."org_code" = :org_code
        GROUP BY date("consumables_counts_installed"."created_at")
        ORDER BY date("consumables_counts_installed"."created_at") DESC
        LIMIT :limit
        SQL;

        return DB::select($query, [
            ':org_code' => $orgCode,
            ':limit' => $limit,
        ]);
    }
}
