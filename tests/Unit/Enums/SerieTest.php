<?php

namespace Tests\Unit\Enums;

use App\Enums\Serie;
use PHPUnit\Framework\TestCase;

class SerieTest extends TestCase
{
    public function test_serie_enum_has_expected_values()
    {
        $expectedSeries = ['s110', 's35', 's120', 's24'];
        $actualSeries = Serie::cases();

        $this->assertCount(count($expectedSeries), $actualSeries);

        foreach ($expectedSeries as $index => $expected) {
            $this->assertEquals($expected, $actualSeries[$index]->name);
        }
    }

    public function test_serie_enum_values()
    {
        $this->assertEquals(110, Serie::s110->value);
        $this->assertEquals(35, Serie::s35->value);
        $this->assertEquals(120, Serie::s120->value);
        $this->assertEquals(24, Serie::s24->value);
    }

    public function test_serie_enum_can_be_created_from_value()
    {
        $s110 = Serie::from(110);
        $s35 = Serie::from(35);
        $s120 = Serie::from(120);
        $s24 = Serie::from(24);

        $this->assertEquals(Serie::s110, $s110);
        $this->assertEquals(Serie::s35, $s35);
        $this->assertEquals(Serie::s120, $s120);
        $this->assertEquals(Serie::s24, $s24);
    }

    public function test_serie_enum_get_all_values()
    {
        $series = array_map(fn ($case) => $case->value, Serie::cases());

        $this->assertIsArray($series);
        $this->assertContains(110, $series);
        $this->assertContains(35, $series);
        $this->assertContains(120, $series);
        $this->assertContains(24, $series);
    }

    public function test_serie_enum_values_are_integers()
    {
        $this->assertIsInt(Serie::s110->value);
        $this->assertIsInt(Serie::s35->value);
        $this->assertIsInt(Serie::s120->value);
        $this->assertIsInt(Serie::s24->value);
    }
}
