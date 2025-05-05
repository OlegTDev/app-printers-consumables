<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * График
 */
class ChartController extends Controller
{


    /**
     * @param int $limit
     * @return array
     */
    public function lastAdded(int $limit = 30)
    {
        $query = <<<SQL
        SELECT
            date("consumables_counts_added"."created_at") AS "date",
            SUM("consumables_counts_added"."count") AS "count"
        FROM "consumables_counts_added"
        WHERE EXISTS (
            SELECT * FROM "consumables_counts"
            INNER JOIN "consumables_counts_organizations" ON "consumables_counts_organizations"."id_consumable_count" = "consumables_counts"."id"
            WHERE "consumables_counts"."id" = "consumables_counts_added"."id_consumable_count"
                AND "consumables_counts_organizations"."org_code" = :org
        )
        GROUP BY date("consumables_counts_added"."created_at")
        ORDER BY date("consumables_counts_added"."created_at") DESC
        LIMIT :limit
        SQL;

        return DB::select($query, [
            ':org' => Auth::user()->org_code,
            ':limit' => $limit,
        ]);
    }

    public function lastInstalled(int $limit = 30)
    {
        $query = <<<SQL
        SELECT
            date("consumables_counts_installed"."created_at") AS "date",
            SUM("consumables_counts_installed"."count") AS "count"
        FROM "consumables_counts_installed"
        WHERE EXISTS (
            SELECT * FROM "consumables_counts"
                INNER JOIN "printers_consumables" ON "printers_consumables"."id_consumable" = "consumables_counts"."id_consumable"
                INNER JOIN "printers_workplace" ON "printers_workplace"."id_printer" = "printers_consumables"."id_printer"	
            WHERE "consumables_counts"."id" = "consumables_counts_installed"."id_consumable_count"
                AND "printers_workplace"."org_code" = :org
        )
        GROUP BY date("consumables_counts_installed"."created_at")
        ORDER BY date("consumables_counts_installed"."created_at") DESC
        LIMIT :limit
        SQL;

        return DB::select($query, [
            ':org' => Auth::user()->org_code,
            ':limit' => $limit,
        ]);
    }




    /**
     * Массив с количеством добавленных 
     * и установленных расходных материалов
     * связанных с текущей организацией
     * @param int $limit
     * @return array
     */
    public function last(int $limit = 30)
    {
        $query = <<<SQL
SELECT 
    "t"."date",
    "t"."count_installed",
    "t"."count_added"
FROM (
    SELECT 
        CASE WHEN "t_added"."date" IS NOT NULL THEN "t_added"."date" ELSE "t_installed"."date" END AS "date",  
        "t_added"."count" AS "count_added",
        "t_installed"."count" AS "count_installed"
    FROM 
    (
        SELECT
            date("consumables_counts_added"."created_at") AS "date",
            SUM("consumables_counts_added"."id") AS "count"
        FROM "consumables_counts_added"
        WHERE EXISTS (
            SELECT * FROM "consumables_counts"
            INNER JOIN "consumables_counts_organizations" ON "consumables_counts_organizations"."id_consumable_count" = "consumables_counts"."id"
            WHERE "consumables_counts"."id" = "consumables_counts_added"."id_consumable_count"
                AND "consumables_counts_organizations"."org_code" = :org
        )
        GROUP BY date("consumables_counts_added"."created_at")
        ORDER BY date("consumables_counts_added"."created_at")
        LIMIT :limit
    ) AS "t_added"
    FULL JOIN (
        SELECT
            date("consumables_counts_installed"."created_at") AS "date",
            SUM("consumables_counts_installed"."id") AS "count"
        FROM "consumables_counts_installed"
        WHERE EXISTS (
            SELECT * FROM "consumables_counts"
                INNER JOIN "printers_consumables" ON "printers_consumables"."id_consumable" = "consumables_counts"."id_consumable"
                INNER JOIN "printers_workplace" ON "printers_workplace"."id_printer" = "printers_consumables"."id_printer"	
            WHERE "consumables_counts"."id" = "consumables_counts_installed"."id_consumable_count"
                AND "printers_workplace"."org_code" = :org
        )
        GROUP BY date("consumables_counts_installed"."created_at")
        ORDER BY date("consumables_counts_installed"."created_at")
        LIMIT :limit
    ) AS "t_installed" ON "t_added"."date" = "t_installed"."date"
) AS "t"
ORDER BY "t"."date" ASC
LIMIT :limit
SQL;
        return DB::select($query, [
            ':org' => Auth::user()->org_code,
            ':limit' => $limit,
        ]);
    }


}
