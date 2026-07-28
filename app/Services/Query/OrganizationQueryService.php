<?php

namespace App\Services\Query;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;

class OrganizationQueryService
{

    public function getAllOrganizationsQuery(): Builder
    {
        return Organization::query();
    }

    public function getUserOrganizationsCodes(bool $isAdmin, int $userId): array
    {
        if ($isAdmin) {
            return Organization::pluck('code')->toArray();
        }

        return Organization::whereHas('users', fn (Builder $query) => $query->where('users_organizations.id_user', $userId))
            ->pluck('code')
            ->toArray();
    }

    public function getOrganizationsByCodes(array $availableCodes): array
    {
        return Organization::query()
            ->whereIn('code', $availableCodes)
            ->get()
            ->toArray();
    }

    public function getOrganizationsTree(array $items): array
    {
        $flatContainer = [];
        $tree = [];

        foreach ($items as $item) {
            $flatContainer[$item['code']] = $this->treeItemMapper($item);
            $flatContainer[$item['code']]['children'] = [];
        }

        foreach ($flatContainer as $code => &$node) {
            $parentCode = $node['data']['parent'];

            if ($parentCode === null) {
                $tree[] = &$node;
            } else {
                if (isset($flatContainer[$parentCode])) {
                    $flatContainer[$parentCode]['children'][] = &$node;
                } else {
                    $tree[] = &$node;
                }
            }
        }

        return $tree;
    }

    private function treeItemMapper(array $item): array
    {
        return [
            'key' => $item['code'],
            'label' => "{$item['name']} ({$item['code']})",
            'code' => $item['code'],
            'data' => [
                'code' => $item['code'],
                'parent' => $item['parent'],
                'name' => $item['name'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
            ],
        ];
    }

}
