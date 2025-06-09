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

class PrintersWorkplaceExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithStrictNullComparison
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

        // подзапрос с количеством установленных картриджей
        $subQueryCountCartridge = DB::table('consumables_counts_installed AS cci_sub_cartridge')
            ->selectRaw('COALESCE(COUNT(cci_sub_cartridge.count), 0)')
            ->join('consumables_counts AS cc_sub_cartridge', 'cc_sub_cartridge.id', '=', 'cci_sub_cartridge.id_consumable_count')
            ->join('consumables AS c_cartridge', 'c_cartridge.id', '=', 'cc_sub_cartridge.id_consumable')
            ->whereColumn('pw.id', '=', 'cci_sub_cartridge.id_printer_workplace')
            ->where('c_cartridge.type', '=', 'cartridge');

        // подзапрос с количеством установленных драм картриджей
        $subQueryCountDrum = DB::table('consumables_counts_installed AS cci_sub_drum')
            ->selectRaw('COALESCE(COUNT(cci_sub_drum.count), 0)')
            ->join('consumables_counts AS cc_sub_drum', 'cc_sub_drum.id', '=', 'cci_sub_drum.id_consumable_count')
            ->join('consumables AS c_drum', 'c_drum.id', '=', 'cc_sub_drum.id_consumable')
            ->whereColumn('pw.id', '=', 'cci_sub_drum.id_printer_workplace')
            ->where('c_drum.type', '=', 'drum');

        // подзапрос с количеством установленных контейнеров для отработанного тонера
        $subQueryCountWasteContainer = DB::table('consumables_counts_installed AS cci_sub_waste_container')
            ->selectRaw('COALESCE(COUNT(cci_sub_waste_container.count), 0)')
            ->join('consumables_counts AS cc_sub_waste_container', 'cc_sub_waste_container.id', '=', 'cci_sub_waste_container.id_consumable_count')
            ->join('consumables AS c_waste_container', 'c_waste_container.id', '=', 'cc_sub_waste_container.id_consumable')
            ->whereColumn('pw.id', '=', 'cci_sub_waste_container.id_printer_workplace')
            ->where('c_waste_container.type', '=', 'wasteContainer');

        // подзапрос с количеством установленных других расходных материалов
        $subQueryCountOther = DB::table('consumables_counts_installed AS cci_sub_other')
            ->selectRaw('COALESCE(COUNT(cci_sub_other.count), 0)')
            ->join('consumables_counts AS cc_sub_other', 'cc_sub_other.id', '=', 'cci_sub_other.id_consumable_count')
            ->join('consumables AS c_other', 'c_other.id', '=', 'cc_sub_other.id_consumable')
            ->whereColumn('pw.id', '=', 'cci_sub_other.id_printer_workplace')
            ->whereNotIn('c_other.type', ['cartridge', 'drum', 'wasteContainer']);

        // фильтр по дате
        if (!$this->withoutPeriod) {
            // cartridge
            $subQueryCountCartridge->where(DB::raw('DATE(cci_sub_cartridge.created_at)'), '>=', $this->dateFrom);
            $subQueryCountCartridge->where(DB::raw('DATE(cci_sub_cartridge.created_at)'), '<=', $this->dateTo);
            // drum
            $subQueryCountDrum->where(DB::raw('DATE(cci_sub_drum.created_at)'), '>=', $this->dateFrom);
            $subQueryCountDrum->where(DB::raw('DATE(cci_sub_drum.created_at)'), '<=', $this->dateTo);
            // wasteContainer
            $subQueryCountWasteContainer->where(DB::raw('DATE(cci_sub_waste_container.created_at)'), '>=', $this->dateFrom);
            $subQueryCountWasteContainer->where(DB::raw('DATE(cci_sub_waste_container.created_at)'), '<=', $this->dateTo);
            // wasteContainer
            $subQueryCountOther->where(DB::raw('DATE(cci_sub_other.created_at)'), '>=', $this->dateFrom);
            $subQueryCountOther->where(DB::raw('DATE(cci_sub_other.created_at)'), '<=', $this->dateTo);
        }

        // основной запрос
        $queryMain = DB::table('printers_workplace AS pw')
            ->select(['pw.id', 'pw.org_code', 'pr.vendor', 'pr.model', 'pr.is_color_print', 'pw.location', 'pw.serial_number', 'pw.inventory_number'])
            ->selectSub($subQueryCountCartridge, 'count_cartridge')
            ->selectSub($subQueryCountDrum, 'count_drum')
            ->selectSub($subQueryCountWasteContainer, 'count_waste_container')
            ->selectSub($subQueryCountOther, 'count_other')
            ->join('printers AS pr', 'pr.id', '=', 'pw.id_printer')
            ->whereIn('pw.org_code', $this->organizations)
            ->orderBy('pw.org_code')
            ->orderBy('pr.vendor')
            ->orderBy('pr.model');

        return $queryMain;
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            ++$this->indexRow,
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
        $rangeHeaderRow = 'A1:L1';
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
