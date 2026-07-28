<?php

namespace App\Exports;


use App\Services\Query\PrintersWorkplaceQueryService;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class PrintersWorkplaceExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithStrictNullComparison
{

    public function __construct(
        private array $organizations,
        private ?string $dateFrom,
        private ?string $dateTo,
        private PrintersWorkplaceQueryService $queryService,
    ) {
    }

    /**
     * @return EloquentBuilder|Relation
     */
    public function query()
    {
        return $this->queryService->buildPrintersWorkplaceInstalledConsumablesByOrganizationsAndPeriod(
            organizations: $this->organizations,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
        );
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->row_num,
            $row->org_code,
            $row->vendor,
            $row->model,
            $row->is_color_print ? 'Да' : 'Нет',
            $row->location,
            $row->serial_number,
            $row->inventory_number,
            $row->count_cartridge,
            $row->count_drum,
            $row->count_waste_container,
            $row->count_other,
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
            'Производитель', // C
            'Модель', // D
            'Цветная печать', // E
            'Кабинет', // F
            'Серийный номер', // G
            'Инвентарный номер', // H
            'Количество установленных картриджей', // I
            'Количество установленных драм-картриджей', // J
            'Количество установленных контейнеров для отработанного тонера', // K
            'Количество установленных других расходных материалов', // L
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
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
        $rangeHeaderRow = 'A1:L1';
        // применение стиля
        $sheet->getStyle($rangeHeaderRow)->applyFromArray($styleArray);
        // фильтр
        $sheet->setAutoFilter($rangeHeaderRow);
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER, // Инвентарный номер
        ];
    }

}
