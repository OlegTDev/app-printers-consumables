<?php

namespace App\Models\Consumable;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Расходный материал
 *
 * @property int $id
 * @property int $id_author
 * @property string $type
 * @property string $name
 * @property string $color
 * @property string $description
 * @property bool $arch
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $author
 * @property Printer[] $printers
 * @property PrinterWorkplace[] $printersWorkplaces
 */
class Consumable extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function(Consumable $model) {
            if (auth()->check()) {
                $model->id_author = auth()->id();
            }
        });
    }

    protected $table = 'consumables';

    protected $fillable = [
        'type',
        'name',
        'color',
        'description',
        'arch',
    ];

    public function author(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'id_author');
    }

    public function printers(): BelongsToMany
    {
        return $this->belongsToMany(Printer::class, 'printers_consumables', 'id_consumable', 'id_printer')->withPivot('id_author');
    }

    public function printersWorkplaces(): BelongsToMany
    {
        return $this->belongsToMany(
            PrinterWorkplace::class,
            'printers_consumables',
            'id_consumable',
            'id_printer',
            'id',
            'id_printer'
        );
    }

    public function printersNotIn(): Builder
    {
        /** @var Builder $printer */
        $printer = Printer::whereDoesntHave('consumables', fn(Builder $query) =>
            $query->where('consumables.id', $this->id)
        );
        return $printer;
    }

    public function consumableCountCurrentOrganization(): HasOne
    {
        return $this->hasOne(ConsumableCount::class, 'id_consumable', 'id')
            ->forCurrentUser();
    }

    public function consumablesCount(): HasMany
    {
        return $this->hasMany(ConsumableCount::class, 'id_consumable', 'id');
    }

    /**
     * Заглушка для работы scope в роуте
     */
    public function counts(): HasMany
    {
        return $this->consumablesCount();
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function (Builder $subQuery, $search) use (&$query) {
            $query->whereAny(['name'], 'ILIKE', "%{$search}%");
        });
    }

    public function scopeWithOtherTypesByPrinter(Builder $query, int $idPrinter): void
    {
        $query->whereHas('printers', function(Builder $query) use($idPrinter) {
            $query->where('printers.id', $idPrinter);
        })
        ->where('type', ConsumableTypesEnum::other->name);
    }

    public static function queryWithoutOtherTypesByPrinter(): Builder
    {
        return static::query()->where('type', '<>', ConsumableTypesEnum::other->name);
    }

    public function title(): string
    {
        $title = ConsumableTypesEnum::getValueByName($this->type) . ' ' . $this->name;
        if ($this->type === 'cartridge') {
            $title .= ' (' . (CartridgeColors::get()[$this->color]['name'] ?? $this->color) . ')';
        }
        return $title;
    }

}
