<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $id_order
 * @property string $status
 * @property string $comment
 * @property int $id_author
 * @property string $created_at
 * 
 * @property Order $order
 * @property User $author
 */
class OrderStatusHistory extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'order_status_history';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }

}
