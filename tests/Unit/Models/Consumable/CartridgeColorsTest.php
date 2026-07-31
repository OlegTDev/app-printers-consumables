<?php

namespace Tests\Unit\Models\Consumable;

use App\Models\Consumable\CartridgeColors;
use PHPUnit\Framework\TestCase;

class CartridgeColorsTest extends TestCase
{
    public function test_it_returns_all_colors(): void
    {
        $colors = CartridgeColors::get();

        $this->assertCount(4, $colors);

        $this->assertArrayHasKey('black', $colors);
        $this->assertEquals('bg-black', $colors['black']['bg']);
    }

    public function test_it_can_get_name_by_color(): void
    {
        $this->assertEquals('Желтый (yellow)', CartridgeColors::getNameByColor('yellow'));
        $this->assertEquals('Пурпурный (magenta)', CartridgeColors::getNameByColor('magenta'));
    }

    public function test_it_can_get_name_by_color_is_empty(): void
    {
        $this->assertNull(CartridgeColors::getNameByColor(null));
        $this->assertNull(CartridgeColors::getNameByColor(''));
        $this->assertNull(CartridgeColors::getNameByColor('green'));
    }

}
