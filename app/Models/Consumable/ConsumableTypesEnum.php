<?php
namespace App\Models\Consumable;

use App\Models\EnumToArrayTrait;

/**
 * Типы расходных материалов
 */
enum ConsumableTypesEnum: string
{
    use EnumToArrayTrait;

    case cartridge = 'Картридж';
    case drum = 'Драм-картридж';
    case wasteContainer = 'Контейнер для отработанного тонера';
    case other = 'Другое';


    public static function getValueByName(string $name): string
    {
        return self::array()[$name] ?? $name;
    }

}
