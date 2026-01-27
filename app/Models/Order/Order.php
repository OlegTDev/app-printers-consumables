<?php

namespace App\Models\Order;

use App\Models\Auth\User;
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
 * @property int $requested_by
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property \App\Models\User $requested
 * @property \App\Models\Order\OrderStatusHistory $statusHistory
 */
final class Order extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    private static $statusLabels = [
        self::STATUS_DRAFT => 'черновик',
        self::STATUS_PENDING => 'на согласовании',
        self::STATUS_REJECTED => 'отказан',
        self::STATUS_COMPLETED => 'завершен',
        self::STATUS_CANCELLED => 'отменен',
        self::STATUS_IN_PROGRESS => 'в работе',
    ];

    public const ROLE_AUTHOR = 'order-author';
    public const ROLE_APPROVER = 'order-approver';


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

    public static function labels()
    {
        return [
            'status' => 'Статус',
            'comment' => 'Комментарий',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'timestamps' => 'Дата',
            'requested_by' => 'Автор',
        ];
    }

    public function requested(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'id_order');
    }

    public static function createWithChildOrder(Model $subOrder, ?string $comment)
    {
        DB::transaction(function () use ($subOrder, $comment) {
            $order = self::create([
                'org_code' => auth()->user()->org_code,
                'status' => self::STATUS_PENDING,
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

    public static function statusLabels(): array
    {
        return self::$statusLabels;
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
