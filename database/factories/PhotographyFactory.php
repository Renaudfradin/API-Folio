<?php

namespace Database\Factories;

use App\Models\Camera;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photography>
 */
class PhotographyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $cameraId = Camera::query()->inRandomOrder()->value('id');

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . fake()->unique()->numberBetween(1, 9999)),
            'date' => fake()->dateTimeBetween('-2 years', 'now'),
            'series' => fake()->word(),
            'city' => fake()->city(),
            'image' => fake()->uuid() . '.jpg',
            'camera_id' => $cameraId,
        ];
    }
}
