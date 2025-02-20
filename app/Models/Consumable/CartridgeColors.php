<?php
namespace App\Models\Consumable;

/**
 * Цвета картриджей
 */
class CartridgeColors
{

    /**
     * @var array
     */
    private static $_colors = [
        'black' => [
            'name' => 'Черный (black)',
            'color' => 'black',
            'bg' => 'bg-black',
        ],
        'blue' => [
            'name' => 'Голубой (blue)',
            'color' => 'blue',
            'bg' => 'bg-blue-500',
        ],
        'yellow' => [
            'name' => 'Желтый (yellow)',
            'color' => 'yellow',
            'bg' => 'bg-yellow-500',
        ],
        'magenta' => [
            'name' => 'Пурпурный (magenta)',
            'color' => 'purple',
            'bg' => 'bg-purple-500',
        ],        
    ];

    /**
     * @return array
     */
    public static function get()
    {
        return self::$_colors;
    }

    /**
     * Описание (name) цвета по его наименованию (color)
     * @param string $color
     * @return string|null
     */
    public static function getNameByColor(string|null $color)
    {
        if (empty($color)) {
            return null;
        }
        $item = array_filter(self::$_colors, fn($val) => $val['color'] == $color);
        return $item[$color]['name'] ?? null;
    }

}