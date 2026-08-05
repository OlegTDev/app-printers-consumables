<?php

namespace Tests\Unit\Models;

use App\Models\Auth\User;
use App\Models\EnumToArrayTrait;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

enum TestEnum: int
{
    use EnumToArrayTrait;

    case one = 1;
    case two = 2;
    case three = 3;
}

class EnumToArrayTest extends TestCase
{
    public function test_it_get_enum_names(): void
    {
        $this->assertEquals(['one', 'two', 'three'], TestEnum::names());
    }

    public function test_it_get_enum_values(): void
    {
        $this->assertEquals([1, 2, 3], TestEnum::values());
    }

    public function test_it_get_enum_as_array(): void
    {
        $this->assertEquals(['one' => 1, 'two' => 2, 'three' => 3], TestEnum::array());
    }

}
