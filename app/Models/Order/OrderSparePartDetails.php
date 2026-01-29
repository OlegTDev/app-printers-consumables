<?php

namespace App\Models\Order;

use App\Models\Printer\PrinterWorkplace;
use App\Models\SpareParts;
use Illuminate\Database\Eloquent\Builder;
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
 * 
 * @property Order $order
 * @property PrinterWorkplace $printerWorkplace
 * @property SpareParts $sparePart
 * @property OrderSparePartDetailsFile $files
 */
class OrderSparePartDetails extends Model
{
    use HasFactory, OrderOrgCodeFilterable;

    public $timestamps = false;

    protected $table = 'order_spare_part_details';

    protected $fillable = [
        'id_printers_workplace',
        'id_spare_part',
        'call_specialist',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function printerWorkplace()
    {
        return $this->hasOne(PrinterWorkplace::class, 'id', 'id_printers_workplace');
    }

    public function sparePart()
    {
        return $this->hasOne(SpareParts::class, 'id', 'id_spare_part');
    }

    public function files()
    {
        return $this->hasMany(OrderSparePartDetailsFile::class, 'id_spare_part_order_detail');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['search'] ?? null, function (Builder $query, $search) {
            $searchTerm = "%$search%";

            $query->with(['sparePart', 'printerWorkplace'])
                ->where(function ($query) use ($searchTerm) {
                    $query
                        ->whereHas('sparePart', function ($q) use ($searchTerm) {
                            $q->whereAny(['name', 'description'], 'ILIKE', $searchTerm);
                        })
                        ->orWhereHas('printerWorkplace', function ($q) use ($searchTerm) {
                            $q->whereAny(['location', 'serial_number', 'inventory_number'], 'ILIKE', $searchTerm)
                                ->orWhereHas('printer', function ($p) use ($searchTerm) {
                                    $p->whereAny(['vendor', 'model'], 'ILIKE', $searchTerm);
                                });
                        });
                });
        });
        $query->when($filters['status'] ?? null, function (Builder $query, $status) {
            $query->whereHas('order', function (Builder $query) use ($status) {
                $query->where('status', $status);
            });
        });
        $query->when($filters['organizations'] ?? [], function (Builder $query, $organizations) {
            $query->whereHas('order', function (Builder $query) use ($organizations) {
                $query->whereIn('org_code', $organizations);
            });
        });
    }

    public function isAmAuthor(): bool
    {
        return auth()->user()->id == $this->order->requested_by;
    }

}
