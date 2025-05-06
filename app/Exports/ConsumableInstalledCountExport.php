<?php

namespace App\Exports;

use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
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
        $subQueryCountInstalled = DB::table('consumables_counts_installed AS cci_sub')
            ->leftJoin('consumables_counts AS cc_sub', 'cc_sub.id', '=', 'cci_sub.id_consumable_count')
            ->leftJoin(
                'consumables_counts_organizations AS cco_sub',
                'cco_sub.id_consumable_count',
                '=',
                'cci_sub.id_consumable_count'
            )
            ->select(DB::raw('COALESCE(SUM(cci_sub.count), 0)'))
            ->whereRaw('cco.org_code = cco_sub.org_code AND cc_sub.id_consumable = c.id');
        if (!$this->withoutPeriod) {
            $subQueryCountInstalled->where(DB::raw('DATE(cci_sub.created_at)'), '>=', $this->dateFrom);
            $subQueryCountInstalled->where(DB::raw('DATE(cci_sub.created_at)'), '<=', $this->dateTo);
        }

        $query = DB::table('consumables AS c')
            ->leftJoin('consumables_counts AS cc', 'c.id', '=', 'cc.id_consumable')
            ->leftJoin(
                'consumables_counts_organizations AS cco',
                'cco.id_consumable_count',
                '=',
                'cc.id'
            )
            ->whereIn('org_code', $this->organizations)
            ->select('cco.org_code', 'c.type', 'c.name', 'c.color', 'c.description', DB::raw('SUM(cc.count) AS count_now'))
            ->selectSub($subQueryCountInstalled, 'sub_query')
            ->groupBy('cco.org_code', 'c.id', 'c.type', 'c.name', 'c.color', 'c.description')
            ->orderBy('cco.org_code')
            ->orderBy('c.type')
            ->orderBy('c.name');

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
            $row->sub_query,
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
