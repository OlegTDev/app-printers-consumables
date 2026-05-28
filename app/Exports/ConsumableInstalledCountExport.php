<?php

namespace App\Exports;

use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Services\Query\ConsumableCountInstalledQueryService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;


class ConsumableInstalledCountExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
{

    public function __construct(
        private array $organizations,
        private string $dateFrom,
        private string $dateTo,
        private ConsumableCountInstalledQueryService $queryService,
    ) {
    }

    /**
     * @return EloquentBuilder|Relation
     */
    public function query()
    {
        return $this->queryService->buildCountInstalled($this->organizations, $this->dateFrom, $this->dateTo);
    }

    public function map($row): array
    {
        return [
            $row->row_num,
            $row->org_code,
            $this->getConsumableType($row->type),
            $row->name,
            $this->getNameByColor($row->color),
            $row->count_installed,
            $row->count_now,
            $row->description,
        ];
    }

    private function getNameByColor(?string $color): ?string
    {
        return CartridgeColors::getNameByColor($color);
    }

    private function getConsumableType(?string $type): string
    {
        return ConsumableTypesEnum::getValueByName($type);
    }

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
        $rangeHeaderRow = 'A1:H1';
        // применение стиля
        $sheet->getStyle($rangeHeaderRow)->applyFromArray($styleArray);
        // фильтр
        $sheet->setAutoFilter($rangeHeaderRow);
    }

}
