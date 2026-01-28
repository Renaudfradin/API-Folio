<?php

namespace Database\Factories;

use App\Enums\Serie;
use App\Models\Camera;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PhotographyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $cameraId = Camera::query()->inRandomOrder()->value('id');

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(1, 9999)),
            'date' => fake()->dateTimeBetween('-2 years', 'now'),
            'series' => fake()->randomElement(Serie::cases())->value,
            'city' => fake()->city(),
            'image' => 'photography/01KG22AX9X5CCA36SPY9HFSWHY.jpg',
            'camera_id' => $cameraId,
        ];
    }
}
