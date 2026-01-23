<?php
namespace App\Models\Order;

use Illuminate\Database\Eloquent\Builder;

trait OrderOrgCodeFilterable
{
    public function scopeFilterByOrgCode(Builder $query)
    {
        if (auth()->user()->hasRole(['admin', Order::ROLE_APPROVER])) {
            return $query;
        }

        return $query->whereHas('order', function (Builder $subQuery) {
            $subQuery->where('org_code', auth()->user()->org_code);
        });
    }

    public static function queryWithFilterByOrgCode()
    {
        return static::query()
            ->with('order')
            ->filterByOrgCode();
    }
}