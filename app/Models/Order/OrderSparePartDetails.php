<?php

namespace App\Models\Order;

use App\Models\Printer\PrinterWorkplace;
use App\Models\SpareParts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Заказы запчастей для принтера
 * @property int $id
 * @property int $id_order
 * @property int $id_printers_workplace
 * @property int $id_spare_part
 * @property bool $call_specialist
 */
class OrderSparePartDetails extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $table = 'order_spare_part_details';

    protected $fillable = [
        'id_printers_workplace',      
        'id_spare_part',      
        'call_specialist',
    ];

    public static function labels()
    {
        return [
            'id_printers_workplace' => 'Принтер',      
            'id_spare_part' => 'Запчасть',
            'call_specialist' => 'Необходимость вызова специалиста', 
            'comment' => 'Комментарий',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function printerWorkplace()
    {
        return $this->hasOne(PrinterWorkplace::class, 'id_printer_workplace');
    }

    public function sparePart()
    {
        return $this->hasOne(SpareParts::class, 'id_spare_part');
    }

}
