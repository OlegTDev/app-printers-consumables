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
 * @property-read Organization $parentOrganization
 * @property-read \Illuminate\Support\Collection<Organization> $childOrganizations
 * @property-read \Illuminate\Support\Collection<User> $users
 * @property string|null $deleted_at
 * @property-read int|null $child_organizations_count
 * @property-read int|null $users_count
 * @method static \Database\Factories\OrganizationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Organization filter(array $filters)
 * @method static Builder<static>|Organization newModelQuery()
 * @method static Builder<static>|Organization newQuery()
 * @method static Builder<static>|Organization query()
 * @method static Builder<static>|Organization whereCode($value)
 * @method static Builder<static>|Organization whereCreatedAt($value)
 * @method static Builder<static>|Organization whereDeletedAt($value)
 * @method static Builder<static>|Organization whereName($value)
 * @method static Builder<static>|Organization whereParent($value)
 * @method static Builder<static>|Organization whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $primaryKey = 'code';

    public $incrementing = false;

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
