<?php

namespace App\Models\Printer;

use App\Models\Auth\User;
use App\Models\Consumable\Consumable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

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
 */
class Printer extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'printers';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'vendor',
        'model',
        'is_color_print',
    ];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'is_color_print' => 'boolean',
    ];    

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function(Printer $model) {
            $model->id_author = Auth::id();
        });        
    }

    /**
     * Описание атрибутов
     * @return array
     */
    public static function labels()
    {
        return [
            'vendor' => 'Производитель',
            'model' => 'Модель',
            'is_color_print' => 'Цветная печать',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'author' => 'Автор',
        ];
    }
    
    public function consumablesDeep()
    {
        return $this->belongsToMany(Consumable::class, 'printers_consumables', 'id_printer', 'id_consumable')
            ->with(['consumableCount' => function($relation) {
                /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
                $relation->leftJoin('consumables_counts_organizations', 'consumables_counts_organizations.id_consumable_count', '=', 'consumables_counts.id')
                    ->where('consumables_counts_organizations.org_code', '=', Auth::user()->org_code);                    
            }]);
    }


    /**
     * Расходные материалы привязанные к текущему принтеру
     * @return BelongsToMany
     */
    public function consumables(): BelongsToMany
    {        
        return $this->belongsToMany(Consumable::class, 'printers_consumables', 'id_printer', 'id_consumable');   
    }

    /**
     * Расходные материалы не привязанные к текущему принтеру
     * @return Builder 
     */
    public function consumablesNotIn(): Builder
    {
        return Consumable::whereDoesntHave('printers', fn(Builder $query) => 
            $query->where('printers.id', $this->id)
        ); 
    }

    /**
     * Автор записи
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author')->withTrashed();
    }    

    /**
     * Фильтр
     * @param Builder $query
     * @param array $filter
     */
    public function scopeFilter(Builder $query, array $filters)
    {               
        $query->with('author');
        $query->when($filters['search'] ?? null, function (Builder $query, $search) {
            $query->whereAny(['vendor', 'model'], 'ILIKE', "%$search%");
        });
        $query->orderByDesc('created_at')->orderByDesc('updated_at');
    }
    
}
