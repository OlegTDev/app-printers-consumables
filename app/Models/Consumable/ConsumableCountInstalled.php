<?php

namespace App\Models\Consumable;

use App\Models\Auth\User;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Запись об установке расходного материала
 *
 * @property int $id
 * @property int $id_consumable_count
 * @property int $id_printer_workplace
 * @property int $id_author
 * @property int $count
 * @property string $created_at
 * @property string $updated_at
 * @property-read ConsumableCount $consumableCount
 * @property-read PrinterWorkplace $printerWorkplace
 * @property-read User $author
 * @method static \Database\Factories\Consumable\ConsumableCountInstalledFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled whereIdAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled whereIdConsumableCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled whereIdPrinterWorkplace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConsumableCountInstalled whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConsumableCountInstalled extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'consumables_counts_installed';

    public function consumableCount(): BelongsTo
    {
        return $this->belongsTo(ConsumableCount::class, 'id_consumable_count');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }

    public function printerWorkplace(): BelongsTo
    {
        return $this->belongsTo(PrinterWorkplace::class, 'id_printer_workplace');
    }

}
