<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExperienceFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->jobTitle();
        $startDate = fake()->dateTimeBetween('-3 years', '-6 months');
        $endDate = fake()->dateTimeBetween($startDate, 'now');

        return [
            'title' => $title,
            'slug' => Str::slug($title.'-'.fake()->unique()->numberBetween(1, 9999)),
            'description' => fake()->paragraphs(3, true),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => fake()->randomElement(['job', 'study', 'freelance']),
        ];
    }
}
