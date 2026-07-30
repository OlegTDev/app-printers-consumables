<?php

namespace App\Models\Printer;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Принтер (справочник)
 *
 * @property int $id
 * @property string $vendor
 * @property string $model
 * @property bool $is_color_print
 * @property int $id_author
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $author
 * @property Consumable[] $consumables
 * @property Consumable[] $consumablesDeep
 * @property PrinterWorkplace[] $printersWorkplaces
 */
class Printer extends Model
{
    use HasFactory;

    protected $table = 'printers';

    protected $fillable = [
        'vendor',
        'model',
        'is_color_print',
    ];

    protected $casts = [
        'is_color_print' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function(Printer $model) {
            if (auth()->check()) {
                $model->id_author = auth()->id();
            }
        });
    }

    public function consumables(): BelongsToMany
    {
        return $this->belongsToMany(Consumable::class, 'printers_consumables', 'id_printer', 'id_consumable')->withPivot('id_author');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author')->withTrashed();
    }

    public function consumablesNotIn(): Builder
    {
        /** @var Builder $query */
        $query = Consumable::whereDoesntHave('printers', fn(Builder $subQuery) =>
            $subQuery->where('printers.id', $this->id)
        );
        return $query;
    }

    public function printersWorkplaces(): HasMany
    {
        return $this->hasMany(PrinterWorkplace::class, 'id_printer');
    }

    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when($filters['search'] ?? null, function (Builder $query, $search) {
            $query->whereAny(['vendor', 'model'], 'ILIKE', "%$search%");
        });
        $query->orderByDesc('created_at')->orderByDesc('updated_at');
    }

}
