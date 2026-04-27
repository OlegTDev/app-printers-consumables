<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\Access\OrganizationAccessService;
use App\Services\Query\OrganizationQueryService;
use App\Services\Query\UserQueryService;


class UsersOrganizationsController extends Controller
{

    public function __construct(private OrganizationAccessService $organizationAccessService)
    {
    }

    private function getUserIdAndIsAdmin(): array
    {
        /** @var \App\Models\Auth\User $auth */
        $auth = auth()->user();
        return [
            $auth->id,
            $auth->isAdmin(),
        ];
    }

    /**
     * @route GET /users/organizations
     */
    public function index(OrganizationQueryService $organizationQueryService): \Illuminate\Http\JsonResponse
    {
        [$userId, $isAdmin] = $this->getUserIdAndIsAdmin();

        $availableOrgCodes = $this->organizationAccessService->getUserAvailableCodes($isAdmin, $userId);
        $availableOrganizations = $organizationQueryService->getOrganizationsByCodes($availableOrgCodes);
        return response()->json([
            'organizations' => $availableOrganizations,
            'labels' => config('labels.organization'),
        ]);
    }

    /**
     * @route POST /users/organizations/{organization}
     */
    public function change(Organization $organization, UserQueryService $userQueryService): \Illuminate\Http\RedirectResponse
    {
        [$userId, $isAdmin] = $this->getUserIdAndIsAdmin();
        $code = $organization->code;

        if ($this->organizationAccessService->isAvailableByOrgCode($code, $isAdmin, $userId)) {
            if ($userQueryService->changeUserOrganization($userId, $code)) {
                return redirect()->back()
                    ->with('success', "Выбрана организация с кодом {$code}!");
            }
            return redirect()->back()
            ->with('error', "Не удалось изменить организацию на {$code}!");
        }
        abort(403, "У пользователя {$userId} нет доступа к организации {$code}");
    }

}
