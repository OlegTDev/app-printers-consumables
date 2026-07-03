<?php
namespace App\Models\Order;

use Illuminate\Database\Eloquent\Builder;

trait OrderOrgCodeFilterable
{
    public function scopeFilterByOrgCode(Builder $query): Builder
    {
        if (auth()->user()->hasRole(['admin', Roles::ORDER_APPROVER->value, Roles::ORDER_EXECUTOR->value])) {
            return $query;
        }

        return $query->whereHas('order', function (Builder $subQuery) {
            $subQuery->where('org_code', auth()->user()->org_code);
        });
    }

    // @deprecated
    public static function queryWithFilterByOrgCode(): Builder
    {
        return static::query()
            ->with('order')
            ->filterByOrgCode();
    }
}
