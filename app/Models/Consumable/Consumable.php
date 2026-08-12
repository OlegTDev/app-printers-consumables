<?php

namespace App\Models\Consumable;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Printer\Printer;
use App\Models\Printer\PrinterWorkplace;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property \Carbon\CarbonInterface $created_at
 * @property \Carbon\CarbonInterface $updated_at
 * @property User $author
 * @property Printer[] $printers
 * @property PrinterWorkplace[] $printersWorkplaces
 * @property-read \Illuminate\Support\Collection<ConsumableCount> $consumablesCount
 * @property-read ConsumableCount|null $consumableCountCurrentOrganization
 * @property string|null $deleted_at
 * @property-read int|null $consumables_count_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consumable\ConsumableCount> $counts
 * @property-read int|null $counts_count
 * @property-read int|null $printers_count
 * @property-read int|null $printers_workplaces_count
 * @method static \Database\Factories\Consumable\ConsumableFactory factory($count = null, $state = [])
 * @method static Builder<static>|Consumable filter(array $filters)
 * @method static Builder<static>|Consumable newModelQuery()
 * @method static Builder<static>|Consumable newQuery()
 * @method static Builder<static>|Consumable query()
 * @method static Builder<static>|Consumable whereColor($value)
 * @method static Builder<static>|Consumable whereCreatedAt($value)
 * @method static Builder<static>|Consumable whereDeletedAt($value)
 * @method static Builder<static>|Consumable whereDescription($value)
 * @method static Builder<static>|Consumable whereId($value)
 * @method static Builder<static>|Consumable whereIdAuthor($value)
 * @method static Builder<static>|Consumable whereName($value)
 * @method static Builder<static>|Consumable whereType($value)
 * @method static Builder<static>|Consumable whereUpdatedAt($value)
 * @method static Builder<static>|Consumable withOtherTypesByPrinter(int $idPrinter)
 * @method static Builder<static>|Consumable withoutOtherTypesByPrinter()
 * @mixin \Eloquent
 */
class Consumable extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    public static function booted()
    {
        static::creating(function(Consumable $model) {
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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author', 'id');
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

    public function scopeWithoutOtherTypesByPrinter(Builder $query): void
    {
        $query->where('type', '<>', ConsumableTypesEnum::other->name);
    }

    public function title(): string
    {
        $title = ConsumableTypesEnum::getValueByName($this->type) . ' ' . $this->name;
        if ($this->type === 'cartridge') {
            $colorName = data_get(CartridgeColors::get(), "{$this->color}.name");
            if ($colorName) {
                $title .= " ({$colorName})";
            }
        }
        return $title;
    }

}
