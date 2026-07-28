<?php

declare(strict_types=1);

namespace App\Services\Query;

use App\Models\Manufacturer;

class ManufacturerQueryService
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Manufacturer>
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Manufacturer::all();
    }
}
