<?php

namespace Database\Factories;

use App\Models\Photography;
use App\Models\PhotographyCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PhotographyCollection>
 */
class PhotographyCollectionFactory extends Factory
{
    protected $model = PhotographyCollection::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(1, 9999)),
            'description' => fake()->optional()->paragraph(),
            'active' => fake()->boolean(70),
        ];
    }

    /**
     * @param  array<int, Photography>|int  $photographies
     */
    public function withPhotographies(array|int $photographies = 3): static
    {
        return $this->afterCreating(function (PhotographyCollection $collection) use ($photographies): void {
            $records = is_int($photographies)
                ? Photography::factory()->count($photographies)->create()
                : collect($photographies);

            $position = 1;
            foreach ($records as $photography) {
                $collection->photographies()->attach($photography, ['position' => $position]);
                $position++;
            }
        });
    }
}
