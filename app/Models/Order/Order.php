<?php

namespace App\Models\Order;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $org_code
 * @property string $status
 * @property string $comment
 * @property int $quantity
 * @property int $requested_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property \App\Models\User $requested
 * @property \App\Models\Order\OrderStatusHistory $statusHistory
 * @property \App\Models\Organization $organization
 */
final class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'comment',
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
        return $this->hasMany(OrderStatusHistory::class, 'id_order');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_code');
    }

    public static function createWithChildOrder(Model $subOrder, ?string $comment, ?int $quantity)
    {
        DB::transaction(function () use ($subOrder, $comment) {
            $order = self::create([
                'org_code' => auth()->user()->org_code,
                'status' => OrderStatusEnum::default(),
                'comment' => $comment,
                'requested_by' => auth()->user()->id,
            ]);

            $subOrder->order()->associate($order);
            $subOrder->save();
        });
    }

    public static function getStatusLabelByStatus(string $status): string
    {
        return self::$statusLabels[$status] ?? $status;
    }

    public function getLastEditor()
    {
        $lastAuthor = $this->statusHistory()->orderBy('created_at','desc')->first();
        if ($lastAuthor) {
            return $lastAuthor->author;
        }
        return null;
    }

    public function setStatus(string $status, string $comment = null)
    {
        $this->status = $status;
        $this->comment = $comment;
        $this->save();
    }

}
