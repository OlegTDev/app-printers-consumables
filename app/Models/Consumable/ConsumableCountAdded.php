<?php

namespace App\Models\Consumable;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * Запись о добавлении расходного материала
 *
 * @property int $id
 * @property int $id_consumable_count
 * @property int $id_author
 * @property int $count
 * @property string $created_at
 * @property string $updated_at
 * @property-read ConsumableCount $consumableCount
 * @property-read User $author
 * @method static \Database\Factories\Consumable\ConsumableCountAddedFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded whereIdAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded whereIdConsumableCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountAdded whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConsumableCountAdded extends Model
{
    use HasFactory;

    protected $table = 'consumables_counts_added';

    protected $fillable = ['id_consumable_count', 'count', 'id_author'];

    public function consumableCount(): BelongsTo
    {
        return $this->belongsTo(ConsumableCount::class, 'id_consumable_count');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }

}
