<?php

namespace App\Models;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
 * @property User[] $users
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_organizations', 'org_code', 'id_user');
    }

}
