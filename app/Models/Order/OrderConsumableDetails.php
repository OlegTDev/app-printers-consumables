<?php

namespace App\Models\Order;

use App\Models\Consumable\Consumable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
class OrderConsumableDetails extends Model
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

}
