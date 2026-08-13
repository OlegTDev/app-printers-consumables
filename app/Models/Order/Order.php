<?php

namespace App\Models\Order;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $org_code
 * @property string $status
 * @property string $comment
 * @property int $quantity
 * @property int $requested_by
 * @property string $service_request_number
 * @property string $service_request_date
 * @property string $created_at
 * @property string $updated_at
 * @property-read User $requested
 * @property-read \Illuminate\Support\Collection<OrderStatusHistory> $statusHistory
 * @property-read Organization $organization
 * @property-read int|null $status_history_count
 * @method static \Database\Factories\Order\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrgCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereServiceRequestDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereServiceRequestNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'comment',
        'service_request_number',
        'service_request_date',
    ];

    public static function booted()
    {
        static::creating(function(self $model) {
            if (auth()->check()) {
                $model->requested_by = auth()->id();
            }
        });
    }

    public function requested(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'id_order')->orderByDesc('created_at');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_code');
    }

    public function setStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

}
