<?php

namespace App\Exports;

use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;


class ConsumableInstalledCountExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
{
    /**
     * Порядковый номер
     * @var int
     */
    private $indexRow = 0;

    /**
     * @param array $organizations    
     */
    public function __construct(
        private array $organizations,
        private bool $withoutPeriod,
        private string $dateFrom,
        private string $dateTo,
    ) {
    }

    /**
     * Запрос
     * {@inheritDoc}
     */
    public function query()
    {
        $query = DB::table('consumables_counts AS cons_counts')
            // SELECT
            ->select([
                'cons.id',
                'cons.type',
                'cons.name',
                'cons.color',
                'cons.description',
                DB::raw('STRING_AGG(DISTINCT cons_counts_org.org_code, \',\') AS org_code'),
                DB::raw('COALESCE(SUM(cons_counts_inst.count), 0) AS count_installed'),
                DB::raw('cons_counts.count AS count_now'),
            ])
            // JOINS
            ->rightJoin('consumables AS cons', 'cons.id', '=', 'cons_counts.id_consumable')
            ->leftJoin('consumables_counts_organizations AS cons_counts_org', 'cons_counts_org.id_consumable_count', '=', 'cons_counts.id')
            ->leftJoin('consumables_counts_installed AS cons_counts_inst', 'cons_counts_inst.id_consumable_count', '=', 'cons_counts.id')
            ->leftJoin('printers_workplace AS pr_wrk', 'pr_wrk.id', '=', 'cons_counts_inst.id_printer_workplace')
            // WHERE
            ->whereIn('cons_counts_org.org_code', $this->organizations)
            ->whereRaw('(pr_wrk.id IS NULL OR pr_wrk.org_code = cons_counts_org.org_code)')
            // GROUP BY
            ->groupBy(['cons.id', 'cons.type', 'cons.name', 'cons.color', 'cons.description', 'cons_counts.count'])
            // ORDER BY
            ->orderBy(DB::raw('STRING_AGG(DISTINCT cons_counts_org.org_code, \',\')'))
            ->orderBy('cons.type')
            ->orderBy('cons.name');


        // Фильтрация по дате
        if (!$this->withoutPeriod) {
            $query->where(function (Builder $query) {
                $query->whereRaw('cons_counts_inst.id IS NULL');
                $query->orWhere(function (Builder $query) {
                    $query->where(DB::raw('DATE(cons_counts_inst.created_at)'), '>=', $this->dateFrom);
                    $query->where(DB::raw('DATE(cons_counts_inst.created_at)'), '<=', $this->dateTo);
                });
            });
        }

        return $query;
    }

    /**
     * @param \stdClass $row
     * @return array
     */
    public function map($row): array
    {
        return [
            ++$this->indexRow,
            $row->org_code,
            $this->getConsumableType($row->type),
            $row->name,
            $this->getNameByColor($row->color),
            $row->count_installed,
            $row->count_now,
            $row->description,
        ];
    }

    /**
     * @param string|null $color
     * @return string|null
     */
    private function getNameByColor($color)
    {
        return CartridgeColors::getNameByColor($color);
    }

    /**
     * @param string $type
     * @return string
     */
    private function getConsumableType($type)
    {
        return ConsumableTypesEnum::getValueByName($type);
    }

    /**
     * Заголовки
     * @return array
     */
    public function headings(): array
    {
        return [
            '#', // A
            'Код организации', // B
            'Тип', // C
            'Наименование', // D
            'Цветная печать', // E
            'Количество установленных расходных материалов', // F
            'Количество оставшихся расходных материалов', // G
            'Описание', // H       
        ];
    }

    /**
     * Стилизация
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @return void
     */
    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $styleArray = [
            'font' => ['bold' => true],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'b6c2d4'],
            ],
        ];

        // первая строка
        $rangeHeaderRow = 'A1:H1';
        // применение стиля
        $sheet->getStyle($rangeHeaderRow)->applyFromArray($styleArray);
        // фильтр
        $sheet->setAutoFilter($rangeHeaderRow);
    }

}
