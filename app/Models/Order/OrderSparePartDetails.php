<?php

namespace App\Models\Order;

use App\Models\Consumable\Consumable;
use App\Models\Consumable\ConsumableTypesEnum;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Заказы запчастей для принтера
 *
 * @property int $id
 * @property int $id_order
 * @property int $id_printers_workplace
 * @property int $id_spare_part
 * @property bool $call_specialist
 * @property-read Order $order
 * @property-read PrinterWorkplace $printerWorkplace
 * @property-read ?Consumable $sparePart
 * @property-read \Illuminate\Support\Collection<OrderSparePartDetailsFile> $files
 * @property-read int|null $files_count
 * @method static \Database\Factories\Order\OrderSparePartDetailsFactory factory($count = null, $state = [])
 * @method static Builder<static>|OrderSparePartDetails filter(array $filters)
 * @method static Builder<static>|OrderSparePartDetails filterByOrgCode()
 * @method static Builder<static>|OrderSparePartDetails newModelQuery()
 * @method static Builder<static>|OrderSparePartDetails newQuery()
 * @method static Builder<static>|OrderSparePartDetails query()
 * @method static Builder<static>|OrderSparePartDetails whereCallSpecialist($value)
 * @method static Builder<static>|OrderSparePartDetails whereId($value)
 * @method static Builder<static>|OrderSparePartDetails whereIdOrder($value)
 * @method static Builder<static>|OrderSparePartDetails whereIdPrintersWorkplace($value)
 * @method static Builder<static>|OrderSparePartDetails whereIdSparePart($value)
 * @mixin \Eloquent
 */
class OrderSparePartDetails extends SubOrderContract
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

    public function printerWorkplace(): BelongsTo
    {
        return $this->belongsTo(PrinterWorkplace::class, 'id_printers_workplace');
    }

    public function sparePart(): ?BelongsTo
    {
        return $this->belongsTo(Consumable::class, 'id_spare_part')
            ->where('type', ConsumableTypesEnum::other->name);
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderSparePartDetailsFile::class, 'id_spare_part_order_detail');
    }

    public function scopeFilter(Builder $query, array $filters): void
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

}
