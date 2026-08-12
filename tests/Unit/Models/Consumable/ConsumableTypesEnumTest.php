<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Consumable\ConsumableTypesEnum;
use Tests\TestCase;

class ConsumableTypesEnumTest extends TestCase
{
    public function test_get_value_by_name_returns_correct_value(): void
    {
        $this->assertEquals(
            'Картридж',
            ConsumableTypesEnum::getValueByName('cartridge')
        );

        $this->assertEquals(
            'Драм-картридж',
            ConsumableTypesEnum::getValueByName('drum')
        );
    }

    public function test_get_value_by_name_returns_input_if_not_found(): void
    {
        $unknownName = 'non_existent_type';

        $this->assertEquals(
            $unknownName,
            ConsumableTypesEnum::getValueByName($unknownName)
        );
    }
}
