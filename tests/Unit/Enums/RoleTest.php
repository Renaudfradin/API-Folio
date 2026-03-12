<?php

namespace Tests\Unit\Enums;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_role_enum_has_expected_values()
    {
        $expectedRoles = ['Admin', 'User', 'Demo', 'Platform'];
        $actualRoles = Role::cases();

        $this->assertCount(count($expectedRoles), $actualRoles);
        
        foreach ($expectedRoles as $index => $expected) {
            $this->assertEquals($expected, $actualRoles[$index]->name);
        }
    }

    public function test_role_enum_values()
    {
        $this->assertEquals('admin', Role::Admin->value);
        $this->assertEquals('user', Role::User->value);
        $this->assertEquals('demo', Role::Demo->value);
        $this->assertEquals('platform', Role::Platform->value);
    }

    public function test_role_enum_can_be_created_from_value()
    {
        $adminRole = Role::from('admin');
        $userRole = Role::from('user');
        $demoRole = Role::from('demo');
        $platformRole = Role::from('platform');

        $this->assertEquals(Role::Admin, $adminRole);
        $this->assertEquals(Role::User, $userRole);
        $this->assertEquals(Role::Demo, $demoRole);
        $this->assertEquals(Role::Platform, $platformRole);
    }

    public function test_role_enum_get_all_values()
    {
        $roles = array_map(fn($case) => $case->value, Role::cases());
        
        $this->assertIsArray($roles);
        $this->assertContains('admin', $roles);
        $this->assertContains('user', $roles);
        $this->assertContains('demo', $roles);
        $this->assertContains('platform', $roles);
    }
}
