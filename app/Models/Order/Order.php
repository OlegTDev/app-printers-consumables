<?php

namespace App\Models\Order;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $status
 * @property string $comment
 * @property int $requested_by
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property \App\Models\User $requested
 */
class Order extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public static function newWithChildOrder(Model $subOrder, ?string $comment)
    {
        DB::transaction(function () use ($subOrder, $comment) {
                        
            $order = self::create([
                'status' => self::STATUS_SUBMITTED,
                'comment' => $comment,
                'requested_by' => Auth::id(),
            ]);

            $subOrder->order()->associate($order);
            $subOrder->save();
        });
    } 

}
