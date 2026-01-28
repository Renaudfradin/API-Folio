<?php

namespace Database\Factories;

use App\Enums\Serie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CameraFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(asText: true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'serie' => fake()->randomElement(Serie::cases())->value,
            'content' => fake()->paragraph(),
            'image' => 'camera/01KG219B0EBXR1EP86NAXM23MA.jpg',
        ];
    }
}
