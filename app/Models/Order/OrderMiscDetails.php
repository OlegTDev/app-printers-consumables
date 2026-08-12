<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $id_order
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property int $id_author
 * @property Order $order
 * @method static \Database\Factories\Order\OrderMiscDetailsFactory factory($count = null, $state = [])
 * @method static Builder<static>|OrderMiscDetails filter(array $filters)
 * @method static Builder<static>|OrderMiscDetails filterByOrgCode()
 * @method static Builder<static>|OrderMiscDetails newModelQuery()
 * @method static Builder<static>|OrderMiscDetails newQuery()
 * @method static Builder<static>|OrderMiscDetails query()
 * @method static Builder<static>|OrderMiscDetails whereCreatedAt($value)
 * @method static Builder<static>|OrderMiscDetails whereDescription($value)
 * @method static Builder<static>|OrderMiscDetails whereId($value)
 * @method static Builder<static>|OrderMiscDetails whereIdAuthor($value)
 * @method static Builder<static>|OrderMiscDetails whereIdOrder($value)
 * @method static Builder<static>|OrderMiscDetails whereName($value)
 * @method static Builder<static>|OrderMiscDetails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderMiscDetails extends SubOrderContract
{
    use HasFactory, OrderOrgCodeFilterable;

    protected $table = 'order_misc_details';

    protected $fillable = [
        'name',
        'description',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['search'] ?? null, function (Builder $query, $search) {
            $searchTerm = "%$search%";
            $query->whereAny(['name', 'description'], 'ILIKE', $searchTerm);
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
