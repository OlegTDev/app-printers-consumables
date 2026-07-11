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
 *
 * @property string $domain
 * @property string $org_code
 * @property string $company
 * @property string $fio
 * @property string $department
 * @property string $post
 * @property string $telephone
 * @property string $lotus_mail
 * @property string $members
 *
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 *
 * @property Role[] $roles
 * @property Permission[] $permissions
 * @property Organization[] $organizations
 *
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

    public function isAvailableByOrgCode(string $orgCode): bool
    {
        return app(OrganizationAccessService::class)->isAvailableByOrgCode(
            orgCode: $orgCode,
            isAdmin: $this->hasRole('admin'),
            userId: $this->id,
        );
    }

    public function availableOrganizations(string $parent = null): array
    {
        $availableOrgCodes = app(OrganizationAccessService::class)->getUserAvailableCodes(
            isAdmin: $this->hasRole('admin'),
            userId: $this->id,
        );

        return app(OrganizationQueryService::class)->getOrganizationsByCodes($availableOrgCodes);
    }


}
