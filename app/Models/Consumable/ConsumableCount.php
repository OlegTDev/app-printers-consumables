<?php

namespace App\Models\Consumable;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Количество расходного материала
 *
 * @property int $id
 * @property int $id_consumable
 * @property int $count
 *
 * @property Consumable $consumable
 * @property ConsumableCountAdded[] $consumablesAdded
 * @property Organization[] $organizations
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


    /**
     * Родительский расходный материал
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function consumable()
    {
        return $this->belongsTo(Consumable::class, 'id_consumable');
    }

    /**
     * Записи, содержащие количество добавленных расходных материалов
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consumablesAdded()
    {
        return $this->hasMany(ConsumableCountAdded::class, 'id_consumable_count')
            ->orderByDesc('created_at');
    }

    /**
     * Записи, содержащие количество установленных расходных материалов
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function consumablesInstalled()
    {
        return $this->hasMany(ConsumableCountInstalled::class, 'id_consumable_count')
            ->orderByDesc('created_at');
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
        $query
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

    /**
     * Привязанные коды организаций к текущей записи
     * @return \Illuminate\Support\Collection
     */
    public function organizationsCodes()
    {
        return DB::table('consumables_counts_organizations')
            ->where('id_consumable_count', $this->id)
            ->pluck('org_code');
    }

    /**
     * Привязанные организации к текущей записи
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'consumables_counts_organizations', 'id_consumable_count', 'org_code');
    }

}
