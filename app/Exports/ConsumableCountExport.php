<?php

namespace App\Exports;

use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Services\Query\ConsumableCountQueryService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;

/**
 * Отчет "Остатки расходных материалов"
 */
class ConsumableCountExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(
        private array $organizations,
        private ConsumableCountQueryService $queryService,
    ) {}

    /**
     * @return EloquentBuilder|Relation
     */
    public function query()
    {
        return $this->queryService->buildConsumableCountByOrganizations($this->organizations);
    }

    public function map($row): array
    {
        return [
            $row->row_num,
            $row->org_code,
            $this->getConsumableType($row->type),
            $row->name,
            $this->getNameByColor($row->color),
            $row->count,
            $row->description,
        ];
    }

    public function headings(): array
    {
        return [
            '#', // A
            'Код организации', // B
            'Тип', // C
            'Наименование', // D
            'Цветная печать', // E
            'Количество', // F
            'Описание', // G
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
        $rangeHeaderRow = 'A1:G1';
        // применение стиля
        $sheet->getStyle($rangeHeaderRow)->applyFromArray($styleArray);
        // фильтр
        $sheet->setAutoFilter($rangeHeaderRow);
    }

    private function getNameByColor(?string $color): ?string
    {
        return CartridgeColors::getNameByColor($color);
    }

    private function getConsumableType(?string $type): string
    {
        return ConsumableTypesEnum::getValueByName($type);
    }

}
