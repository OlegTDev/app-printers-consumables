<?php

namespace App\Models\Order;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

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
 *
 * @property User $requested
 * @property OrderStatusHistory $statusHistory
 * @property Organization $organization
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

    public static function boot()
    {
        parent::boot();
        self::creating(function(self $model) {
            $model->requested_by = Auth::id();
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

    public function setStatus(string $status, string $comment = null)
    {
        $this->status = $status;
        $this->comment = $comment;
        $this->save();
    }

}
