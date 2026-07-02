<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Организация
 *
 * @property string $code
 * @property string $parent
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organization $parentOrganization
 * @property Organization[] $childOrganizations
 */
class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'parent',
        'name',
    ];

    public function parentOrganization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class,  'parent');
    }

    public function childOrganizations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Organization::class, 'parent', 'code');
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function (Builder $subQuery, $search) use (&$query) {
            $query->whereAny(['code', 'name'], 'ILIKE', "%{$search}%");
        });
    }

}
