<?php

namespace App\Models\Consumable;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Количество расходного материала
 *
 * @property int $id
 * @property int $id_consumable
 * @property int $count
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Consumable $consumable
 * @property-read \Illuminate\Database\Eloquent\Collection|ConsumableCountAdded[] $consumablesAdded
 * @property-read \Illuminate\Database\Eloquent\Collection|Organization[] $organizations
 */
class ConsumableCount extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'consumables_counts';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'id_consumable',
        'count',
    ];


    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class, 'id_consumable');
    }

    public function consumablesAdded(): HasMany
    {
        return $this->hasMany(ConsumableCountAdded::class, 'id_consumable_count')
            ->orderByDesc('created_at');
    }

    // заглушка для роутов
    public function addeds(): HasMany
    {
       return $this->consumablesAdded();
    }

    public function consumablesInstalled(): HasMany
    {
        return $this->hasMany(ConsumableCountInstalled::class, 'id_consumable_count')
            ->orderByDesc('created_at');
    }

    public function installeds(): HasMany
    {
        return $this->consumablesInstalled();
    }

    /**
     * Описание атрибутов
     * @return array
     */
    public static function labels()
    {
        return [
            'id_consumable' => 'Расходный материал',
            'count' => 'Количество',
            'selectedOrganizations' => 'Организации',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    public function scopeForCurrentUser(Builder $query)
    {
        return $query->whereHas('organizations', function(Builder $q) {
            $q->where('org_code', auth()->user()->org_code);
        });
    }


    public function scopeFilter(Builder $query, array $filters)
    {
        return $query
            ->with(['consumable'])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->whereHas('consumable', function($query) use ($search) {
                    $query->where('name', 'ILIKE', "%$search%");
                });
            })
            ->when($filters['consumableType'] ?? null, function (Builder $query, $consumableType) {
                $query->whereHas('consumable', function($query) use ($consumableType) {
                    $query->where('type', $consumableType);
                });
            })
            ->orderByDesc('created_at')->orderByDesc('updated_at');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'consumables_counts_organizations', 'id_consumable_count', 'org_code');
    }

    public function organizationsCodes(): \Illuminate\Support\Collection
    {
        return $this->organizations->pluck("code");
    }

}
