<?php

namespace Tests\Unit\Enums;

use App\Enums\Platform;
use PHPUnit\Framework\TestCase;

class PlatformTest extends TestCase
{
    public function test_platform_enum_has_expected_values()
    {
        $expectedPlatforms = [
            'WelcomeToTheJungle',
            'Linkedin',
            'Indeed',
            'Glassdoor',
            'Jobteaser',
            'JobupCh',
            'Mail',
            'PoleEmploi',
            'Other'
        ];
        $actualPlatforms = Platform::cases();

        $this->assertCount(count($expectedPlatforms), $actualPlatforms);

        foreach ($expectedPlatforms as $index => $expected) {
            $this->assertEquals($expected, $actualPlatforms[$index]->name);
        }
    }

    public function test_platform_enum_values()
    {
        $this->assertEquals('welcometothejungle', Platform::WelcomeToTheJungle->value);
        $this->assertEquals('linkedin', Platform::Linkedin->value);
        $this->assertEquals('indeed', Platform::Indeed->value);
        $this->assertEquals('glassdoor', Platform::Glassdoor->value);
        $this->assertEquals('jobteaser', Platform::Jobteaser->value);
        $this->assertEquals('jobup.ch', Platform::JobupCh->value);
        $this->assertEquals('mail', Platform::Mail->value);
        $this->assertEquals('pole-emploi', Platform::PoleEmploi->value);
        $this->assertEquals('other', Platform::Other->value);
    }

    public function test_platform_enum_can_be_created_from_value()
    {
        $welcomePlatform = Platform::from('welcometothejungle');
        $linkedinPlatform = Platform::from('linkedin');
        $otherPlatform = Platform::from('other');

        $this->assertEquals(Platform::WelcomeToTheJungle, $welcomePlatform);
        $this->assertEquals(Platform::Linkedin, $linkedinPlatform);
        $this->assertEquals(Platform::Other, $otherPlatform);
    }

    public function test_platform_enum_get_all_values()
    {
        $platforms = array_map(fn($case) => $case->value, Platform::cases());

        $this->assertIsArray($platforms);
        $this->assertContains('welcometothejungle', $platforms);
        $this->assertContains('linkedin', $platforms);
        $this->assertContains('indeed', $platforms);
        $this->assertContains('glassdoor', $platforms);
        $this->assertContains('jobteaser', $platforms);
        $this->assertContains('jobup.ch', $platforms);
        $this->assertContains('mail', $platforms);
        $this->assertContains('pole-emploi', $platforms);
        $this->assertContains('other', $platforms);
    }
}
