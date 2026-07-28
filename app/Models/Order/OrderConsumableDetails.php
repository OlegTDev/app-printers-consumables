<?php

namespace App\Models\Order;

use App\Models\Consumable\Consumable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Заказы картриджей для принтера
 * @property int $id
 * @property int $id_order
 * @property int $id_consumable
 * @property int $id_author
 * @property int $quantity
 *
 * @property Order $order
 */
class OrderConsumableDetails extends SubOrderContract
{
    use HasFactory, OrderOrgCodeFilterable;

    public $timestamps = false;

    protected $table = 'order_consumables_details';

    protected $fillable = [
        'id_consumable',
        'quantity',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class, 'id_consumable');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['search'] ?? null, function (Builder $query, $search) {
            $searchTerm = "%$search%";

            $query->with(['consumable'])
                ->where(function (Builder $query) use ($searchTerm) {
                    $query
                        ->whereHas('consumable', function (Builder $query) use ($searchTerm) {
                            $query->whereAny(['name', 'description'], 'ILIKE', $searchTerm);
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
