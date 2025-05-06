<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ConsumableInstalledCountExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithStrictNullComparison
{
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
        $subQueryCountInstalled = DB::table('consumables_counts_installed AS cci')
            ->leftJoin(
                'consumables_counts_organizations AS cco_sub',
                'cco_sub.id_consumable_count',
                '=',
                'cci.id_consumable_count'
            )
            ->select(DB::raw('COALESCE(SUM(cci.count), 0)'))
            ->whereRaw('cco.org_code = cco_sub.org_code');
        if (!$this->withoutPeriod) {
            $subQueryCountInstalled->where(DB::raw('DATE(cci.created_at)'), '>=', $this->dateFrom);
            $subQueryCountInstalled->where(DB::raw('DATE(cci.created_at)'), '<=', $this->dateTo);
        }


        $query = DB::table('consumables_counts AS cc')
            ->leftJoin(
                'consumables_counts_organizations AS cco',
                'cco.id_consumable_count',
                '=',
                'cc.id'
            )
            ->whereIn('org_code', $this->organizations)
            ->select('cco.org_code', DB::raw('SUM(cc.count) AS count_now'))
            ->selectSub($subQueryCountInstalled, 'sub_query')
            ->groupBy('cco.org_code')
            ->orderBy('cco.org_code');

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
            $row->sub_query,
            $row->count_now,
        ];
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
            'Количество установленных расходных материалов', // C
            'Количество оставшихся расходных материалов', // D            
        ];
    }

    /**
     * Формат колонок
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            // 'C' => NumberFormat::FORMAT_NUMBER,
            // 'D' => NumberFormat::FORMAT_NUMBER,
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
        $rangeHeaderRow = 'A1:D1';
        // применение стиля
        $sheet->getStyle($rangeHeaderRow)->applyFromArray($styleArray);
        // фильтр
        $sheet->setAutoFilter($rangeHeaderRow);
    }

}
