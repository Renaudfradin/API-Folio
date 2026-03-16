<?php

namespace Tests\Unit\Enums;

use App\Enums\Stack;
use PHPUnit\Framework\TestCase;

class StackTest extends TestCase
{
    public function test_stack_enum_has_expected_values()
    {
        $expectedStacks = [
            'Laravel', 'Nuxt', 'Next', 'React', 'Vue',
            'Symfony', 'Wordpress', 'Node', 'Python', 'PHP', 'Other', 'ThreeJS',
        ];
        $actualStacks = Stack::cases();

        $this->assertCount(count($expectedStacks), $actualStacks);

        foreach ($expectedStacks as $index => $expected) {
            $this->assertEquals($expected, $actualStacks[$index]->name);
        }
    }

    public function test_stack_enum_values()
    {
        $this->assertEquals('laravel', Stack::Laravel->value);
        $this->assertEquals('nuxt', Stack::Nuxt->value);
        $this->assertEquals('next', Stack::Next->value);
        $this->assertEquals('react', Stack::React->value);
        $this->assertEquals('vue', Stack::Vue->value);
        $this->assertEquals('symfony', Stack::Symfony->value);
        $this->assertEquals('wordpress', Stack::Wordpress->value);
        $this->assertEquals('node', Stack::Node->value);
        $this->assertEquals('python', Stack::Python->value);
        $this->assertEquals('php', Stack::PHP->value);
        $this->assertEquals('other', Stack::Other->value);
        $this->assertEquals('three.js', Stack::ThreeJS->value);
    }

    public function test_stack_enum_can_be_created_from_value()
    {
        $laravel = Stack::from('laravel');
        $react = Stack::from('react');
        $python = Stack::from('python');
        $threejs = Stack::from('three.js');

        $this->assertEquals(Stack::Laravel, $laravel);
        $this->assertEquals(Stack::React, $react);
        $this->assertEquals(Stack::Python, $python);
        $this->assertEquals(Stack::ThreeJS, $threejs);
    }

    public function test_stack_enum_get_all_values()
    {
        $stacks = array_map(fn ($case) => $case->value, Stack::cases());

        $this->assertIsArray($stacks);
        $this->assertContains('laravel', $stacks);
        $this->assertContains('react', $stacks);
        $this->assertContains('python', $stacks);
        $this->assertContains('three.js', $stacks);
        $this->assertContains('other', $stacks);
    }

    public function test_stack_enum_values_are_strings()
    {
        $this->assertIsString(Stack::Laravel->value);
        $this->assertIsString(Stack::React->value);
        $this->assertIsString(Stack::Python->value);
        $this->assertIsString(Stack::Other->value);
    }
}
