<?php

namespace App\Models\Printer;

use App\Models\Auth\User;
use App\Models\Consumable\ConsumableCountInstalled;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read Printer $printer
 * @property-read Organization $organization
 * @property-read User $author
 * @property-read ConsumableCountInstalled[] $consumableCountInstalled
 */
class PrinterWorkplace extends Model
{
    use HasFactory;

    protected $table = 'printers_workplace';

    protected $fillable = [
        'id_printer',
        'location',
        'serial_number',
        'inventory_number',
        'org_code',
    ];

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function(self $model) {
            if (auth()->check()) {
                $model->id_author = auth()->id();
            }
        });
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class, 'id_printer');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_code');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }

    public function consumableCountInstalled(): HasMany
    {
        return $this->hasMany(ConsumableCountInstalled::class, 'id_printer_workplace');
    }


    public function scopeForCurrentUser(Builder $query): void
    {
        $query->where('org_code', auth()->user()->org_code);
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function (Builder $subQuery, $search) {
            $subQuery->where(fn($query) =>
                $query->whereAny(['location', 'serial_number', 'inventory_number'], 'ILIKE', "%$search%")
                ->orWhereHas('printer', fn($query) =>
                    $query->whereAny(['vendor', 'model'], 'ILIKE', "%$search%")
                )
            );
        });
    }


}
