<?php

namespace Database\Factories;

use App\Enums\Stack;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(1, 9999)),
            'description' => fake()->sentence(12),
            'image' => 'project/01KF0Q12A0YF08DEZCMCVVJWPX.jpg',
            'url' => fake()->url(),
            'url_github' => fake()->url(),
            'stack' => (string) fake()->randomElement(Stack::cases())->value,
        ];
    }
}
