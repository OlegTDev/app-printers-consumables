<?php

namespace App\Exports;

use App\Models\Printer\PrinterWorkplace;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PrintersWorkplaceExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting
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
        return PrinterWorkplace::with('printer')->whereIn('org_code', $this->organizations);
    }

    /**
     * @param PrinterWorkplace $row
     * @return array
     */
    public function map($row): array
    {
        return [
            ++$this->indexRow,
            $row->org_code,
            $row->printer->vendor,
            $row->printer->model,
            $row->printer->is_color_print ? 'Да' : 'Нет',
            $row->location,
            $row->serial_number,
            $row->inventory_number,
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

    /**
     * Формат колонок
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER, // Инвентарный номер
        ];
    }
    
}
