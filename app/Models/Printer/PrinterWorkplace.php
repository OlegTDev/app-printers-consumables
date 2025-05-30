<?php

namespace App\Models\Printer;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Принтер (на рабочем месте)
 * 
 * @property int $id
 * @property int $id_printer
 * @property int $id_author
 * @property string $org_code
 * @property string $location
 * @property string $serial_number
 * @property string $inventory_number
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property Printer $printer
 * @property Organization $organization
 * @property User $author
 */
class PrinterWorkplace extends Model
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'printers_workplace';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'id',
        'id_printer',
        'location',
        'serial_number',
        'inventory_number',
    ];

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function(self $model) {
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
            'id_printer' => 'Принтер',
            'org_code' => 'Организация',
            'location' => 'Кабинет',
            'serial_number' => 'Серийный номер',
            'inventory_number' => 'Инвентарный номер',
            'date' => 'Дата',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'author' => 'Автор',
        ];
    }

    /**
     * Принтер (справочник)
     * @return BelongsTo
     */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class, 'id_printer');
    }

    /**
     * Организация
     * @return BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_code');
    }

    /**
     * Автор записи
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }    

    /**
     * Фильтр
     * @param Builder $query
     * @param array $filter
     */
    public function scopeFilter(Builder $query, array $filters)
    {   
        $query           
            ->with(['printer' => function($relation) {
                /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
                $relation->with(['consumablesDeep']);
            }])
            ->where('org_code', Auth::user()->org_code)
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query
                    ->where(fn($query) =>
                        $query->whereAny(['location', 'serial_number', 'inventory_number'], 'ILIKE', "%$search%")
                        ->orWhereHas('printer', fn($query) => 
                            $query->whereAny(['vendor', 'model'], 'ILIKE', "%$search%")
                        )
                    );
            })
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at');
    }

    /**
     * Установленные расходные материалы для текущего принтера
     * @return \Illuminate\Support\Collection
     */
    public function consumablesInstalled(): Collection
    {
        $query = DB::table('consumables_counts_installed AS cci')
            ->select([
                'c.type', 'c.name', 'c.color', 'c.description', 
                'u.name AS user_name', 'u.fio AS user_fio', 'u.department AS user_department', 'u.post AS user_post',
                'cci.created_at AS date_installed', 'cci.id', 'cci.count',
            ])
            ->rightJoin('consumables_counts AS cc', 'cc.id', '=', 'cci.id_consumable_count')
            ->rightJoin('consumables AS c', 'c.id', '=', 'cc.id_consumable')            
            ->leftJoin('users AS u', 'u.id', '=', 'cci.id_author')
            ->where('id_printer_workplace', $this->id)
            ->orderByDesc('cci.created_at')
            ->get();
        return $query;
    }

}
