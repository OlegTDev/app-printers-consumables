<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Auth\LdapUser;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Services\Query\OrganizationQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

/**
 * Управление пользователями
 */
class UsersController extends Controller
{
    use BuildsListQuery;


    /**
     * @route GET /users
     */
    public function index(Request $request): \Inertia\Response
    {
        $paginatedData = $this->getPaginatedData(
            request: $request,
            query: User::with(['roles', 'organizations'])->withTrashed(),
            filterFields: ['search', 'roles'],
            resourceClass: UserResource::class,
        );

        $roles = RoleResource::collection(Role::all());

        return Inertia::render('Users/Index', [
            ...$paginatedData,
            'roles' => $roles,
        ]);
    }

    /**
     * @route /users/create
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Users/Create');
    }

    /**
     * @route POST /users
     */
    public function store(UserCreateRequest $request, LdapUser $ldapUser): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->safe();

        $username = $validated->string('name');
        $domain = 'default';

        if (!$user = $ldapUser->findOrCreate($username, $domain)) {
            return back()->with('error', "Пользователь {$request->name} не найден!");
        }

        return to_route('users.edit', [$user->id])
            ->with('success', 'Пользователь успешно создан!');
    }

    /**
     * @route GET /users/edit/{$user}
     */
    public function edit(User $user, OrganizationQueryService $organizationQueryService): \Inertia\Response
    {
        Gate::authorize('edit', $user);

        $user->load(['roles', 'organizations']);
        $roles = RoleResource::collection(Role::all());

        $availableOrganizations = auth()->user()->availableOrganizations();
        $organizations = $organizationQueryService->getOrganizationsTree($availableOrganizations);

        return Inertia::render('Users/Edit', [
            'user' => UserResource::make($user),
            'roles' => $roles,
            'organizations' => $organizations,
            'labels' => config('labels.user'),
        ]);
    }

    /**
     * @route PUT /users/{user}
     */
    public function update(User $user, UserUpdateRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->safe();

        $user->updateRoles($validated->array('selectedRoles'));
        $user->updateOrganizations($validated->array('selectedOrganizations'));

        return back()->with('success', 'Данные пользователя обновлены.');
    }

    /**
     * @route DELETE /users/{id}
     */
    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->delete();

        return back()->with('success', 'Пользователь удален.');
    }

    /**
     * @route PUT /users/{user}/restore
     */
    public function restore(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->restore();

        return back()->with('success', 'Пользователь восстановлен.');
    }
}
