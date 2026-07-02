<?php

namespace App\Services\Query;

use App\Models\Organization;

class OrganizationQueryService
{

    public function getUserOrganizationsCodes(bool $isAdmin, int $userId): array
    {
        $query = Organization::query();
        if (!$isAdmin) {
            $query->join('users_organizations', 'users_organizations.org_code', '=', 'organizations.code')
                ->where('users_organizations.id_user', $userId);
        }
        return $query->pluck('organizations.code')->toArray();
    }

    public function getOrganizationsByCodes(array $availableCodes): array
    {
        return Organization::query()
            ->whereIn('code', $availableCodes)
            ->get()
            ->toArray();
    }

    public function getAllOrganizationsQuery()
    {
        return Organization::query();
    }

    public function getOrganizationsTree(array $items)
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
                $tree[$code] = &$node;
            } else {
                if (isset($flatContainer[$parentCode])) {
                    $flatContainer[$parentCode]['children'][$code] = &$node;
                } else {
                    $tree[$code] = &$node;
                }
            }
        }

        return $this->cleanKeys($tree);
    }

    private function treeItemMapper($item)
    {
        return [
            'key' => $item['code'],
            'data' => [
                'code' => $item['code'],
                'parent' => $item['parent'],
                'name' => $item['name'],
                'created_at' => $item['created_at'],
                'updated_at' => $item['updated_at'],
            ],
        ];
    }

    private function cleanKeys(array $tree): array
    {
        $result = array_values($tree);
        foreach ($result as $key => $item) {
            if (isset($item['children']) && !empty($item['children'])) {
                $result[$key]['children'] = $this->cleanKeys($item['children']);
            } else {
                $result[$key]['children'] = [];
            }
        }
        return $result;
    }

}
