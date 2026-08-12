<?php

namespace App\Models\Auth;

use App\Models\Organization;
use App\Services\Access\OrganizationAccessService;
use App\Services\Query\OrganizationQueryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;


/**
 * Пользователь
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $photo_path
 * @property string $password
 * @property string $domain
 * @property string $org_code
 * @property string $company
 * @property string $fio
 * @property string $department
 * @property string $post
 * @property string $telephone
 * @property string $lotus_mail
 * @property string $members
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property-read \Illuminate\Support\Collection<Role> $roles
 * @property-read \Illuminate\Support\Collection<Permission> $permissions
 * @property-read \Illuminate\Support\Collection<Organization> $organizations
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property string|null $guid
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $organizations_count
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\Auth\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User filter(array $filters)
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User onlyTrashed()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereCompany($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereDeletedAt($value)
 * @method static Builder<static>|User whereDepartment($value)
 * @method static Builder<static>|User whereDomain($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereFio($value)
 * @method static Builder<static>|User whereGuid($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereLotusMail($value)
 * @method static Builder<static>|User whereMembers($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User whereOrgCode($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePhotoPath($value)
 * @method static Builder<static>|User wherePost($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereTelephone($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements LdapAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, UserRolesTrait, UserOrganizationsTrait;
    use AuthenticatesWithLdap;

    protected $fillable = [
        'name',
        'email',
        'password',
        'org_code',
        'domain',
        'company',
        'fio',
        'department',
        'post',
        'telephone',
        'lotus_mail',
        'members',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'owner' => 'boolean',
        'email_verified_at' => 'datetime',
        'members' => 'array',
    ];

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereAny(['name', 'email', 'fio'], 'ilike', '%'.$search.'%');
            });
        })
        ->when($filters['roles'] ?? null, function(Builder $query, $roles) {
            $query->whereHas('roles', function (Builder $query) use ($roles) {
                $query->whereIn('name', $roles);
            });
        });
    }

    public function availableOrganizations(): array
    {
        $availableOrgCodes = app(OrganizationAccessService::class)->getUserAvailableCodes(
            isAdmin: $this->hasRole('admin'),
            userId: $this->id,
        );

        return app(OrganizationQueryService::class)->getOrganizationsByCodes($availableOrgCodes);
    }


}
