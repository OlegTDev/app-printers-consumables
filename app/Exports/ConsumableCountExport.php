<?php

namespace App\Exports;

use App\Models\Consumable\CartridgeColors;
use App\Models\Consumable\ConsumableTypesEnum;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;

class ConsumableCountExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    private $indexRow = 0;

    /**
     * @param array $organizations    
     */
    public function __construct(
        private array $organizations,     
    ) {}

    /**
     * Запрос
     * {@inheritDoc}
     */
    public function query()
    {        
        return DB::table('consumables_counts')
            ->select([
                'consumables_counts.count', 
                'consumables_counts_organizations.org_code', 
                'consumables.type',
                'consumables.name',
                'consumables.color',
                'consumables.description',
            ])
            ->leftJoin('consumables_counts_organizations', 'consumables_counts_organizations.id_consumable_count', '=', 'consumables_counts.id')
            ->rightJoin('consumables', 'consumables.id', '=', 'consumables_counts.id_consumable')
            ->whereIn('org_code', $this->organizations)
            ->orderBy('org_code', 'asc')
            ->orderBy('name', 'asc');
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
            $row->count,
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
            'Количество', // F
            'Описание', // G
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
        $rangeHeaderRow = 'A1:G1';
        // применение стиля
        $sheet->getStyle($rangeHeaderRow)->applyFromArray($styleArray);
        // фильтр
        $sheet->setAutoFilter($rangeHeaderRow);
    }

}
