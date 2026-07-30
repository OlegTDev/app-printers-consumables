<?php

namespace Database\Seeders\Concerns;

trait HasRegionCode
{
    protected function getRegionCode(?string $default = null): string
    {
        $code = config('app.region_code', $default);
        if (empty($code)) {
            throw new \Exception('Необходимо указать код региона "REGION_CODE" в файле .env!');
        }
        return $code;
    }
}
