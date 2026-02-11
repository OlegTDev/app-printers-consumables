<?php

namespace App\Models\Auth;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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
    use Notifiable, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'org_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'owner' => 'boolean',
        'email_verified_at' => 'datetime',
        'members' => 'array',
    ];

    /**
     * @return string
     */
    public static function getOrgCodeFromUsername(string $username, string $defaultCode = '0000'): string
    {
        if (preg_match('/^n?\d{4}/i', $username, $matches) && isset($matches[0])) {
            return $matches[0];
        }
        return $defaultCode;
    }

    /**
     * @param Builder $query
     * @param array $filter
     * @return void
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->whereNotNull('users.id')
            ->withTrashed()
            ->select('users.*');

        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('fio', 'like', '%'.$search.'%');
            });
        })
        ->when($filters['role'] ?? null, function(Builder $query, $role) {
            $query->leftJoin('roles_users', 'roles_users.id_user', '=', 'users.id')
                ->leftJoin('roles', 'roles.id', '=', 'roles_users.id_role')
                ->where('roles.name', $role);
        });
    }

    /**
     * Изменение организации у пользователя
     * @param string $code код организации
     * @return void
     */
    public function changeSelectedOrganization(string $code)
    {
        $this->org_code = $code;
        $this->save();
    }

    /**
     * Проверка доступа к организации $orgCode у текущего пользователя
     * @param string $orgCode
     * @return bool
     */
    public function isAvailableByOrgCode(string $orgCode): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }
        return Organization::query()
            ->join('users_organizations', 'users_organizations.org_code', '=', 'organizations.code')
            ->where('users_organizations.id_user', '=', $this->id)
            ->where('organizations.code', '=', $orgCode)
            ->exists();
    }

    /**
     * Вложенный список доступных организаций
     * @param string $parent код родительской организации
     * @return array
     */
    public function availableOrganizations(string $parent = null): array
    {
        $isAdmin = $this->hasRole('admin');
        $result = [];
        $items = Organization::query()
            ->select(['organizations.code', 'organizations.name', 'users_organizations.id AS available'])
            ->leftJoin('users_organizations', function($join) {
                $join->on('users_organizations.org_code', '=', 'organizations.code');
                $join->on('users_organizations.id_user', '=', DB::raw($this->id));
            })
            ->where('organizations.parent', '=', $parent)
            ->get();

        if ($items !== null) {
            foreach($items as $item) {
                $children = $this->availableOrganizations($item->code);
                if ($isAdmin || !empty($item->available)) {
                    $result[] = [
                        'key' => $item->code,
                        'label' => "{$item->name} ({$item->code})",
                        'code' => $item->code,
                        'data' => [
                            'code' => $item->code,
                            'name' => $item->name,
                        ],
                        'children' => $children,
                    ];
                }
                elseif ($children != null) {
                    $result = $children;
                }
            }
        }
        return $result;
    }


}
