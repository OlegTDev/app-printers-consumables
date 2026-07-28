<?php

namespace App\Http\Controllers\Dictionary;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\BuildsListQuery;
use App\Http\Requests\Dictionary\OrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\Query\OrganizationQueryService;
use Inertia\Inertia;
use Illuminate\Http\Request;

/**
 * Управление организациями
 */
class OrganizationsController extends Controller
{
    use BuildsListQuery;

    /**
     * @route GET /dictionary/organizations
     */
    public function index(OrganizationQueryService $queryService, Request $request): \Inertia\Response
    {
        $organizations = $queryService->getAllOrganizationsQuery();
        $paginatedData = $this->getPaginatedData(
            request: $request,
            query: $organizations,
            transformCallback: function($data) use ($queryService) {
                $data['data'] = $queryService->getOrganizationsTree($data['data']);
                return $data;
            },
        );

        return Inertia::render('Dictionary/Organizations/Index', [
            ...$paginatedData,
            'labels' => config('labels.organization'),
        ]);
    }

    /**
     * @route GET /dictionary/organizations/create
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Dictionary/Organizations/Create', [
            'labels' => config('labels.organization'),
        ]);
    }

    /**
     * @route POST /dictionary/organizations
     */
    public function store(OrganizationRequest $request): \Illuminate\Http\RedirectResponse
    {
        Organization::create($request->validated());

        return to_route('dictionary.organizations.index')
            ->with('success', 'Запись успешно добавлена!');
    }

    /**
     * @route GET /dictionary/organizations/{organization}
     */
    public function show(Organization $organization): \Inertia\Response
    {
        return Inertia::render('Dictionary/Organizations/Show', [
            'organization' => new OrganizationResource($organization),
            'labels' => config('labels.organization'),
        ]);
    }

    /**
     * @route GET /dictionary/organizations/{organization}/edit
     */
    public function edit(Organization $organization): \Inertia\Response
    {
        return Inertia::render('Dictionary/Organizations/Edit', [
            'organization' => new OrganizationResource($organization),
            'labels' => config('labels.organization'),
        ]);
    }

    /**
     * @route PUT /dictionary/organizations/{organization}
     */
    public function update(OrganizationRequest $request, Organization $organization): \Illuminate\Http\RedirectResponse
    {
        $organization->update($request->validated());

        return to_route('dictionary.organizations.index')
            ->with('success', 'Запись успешно обновлена!');
    }

    /**
     * @route DELETE /dictionary/organizations/{organization}
     */
    public function destroy(Organization $organization): \Illuminate\Http\RedirectResponse
    {
        $organization->delete();

        return to_route('dictionary.organizations.index')
            ->with('success', 'Запись успешно удалена.');
    }

}
