<?php

use App\Models\Camera;
use App\Models\Photography;
use App\Models\PhotographyCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only active collections', function () {
    PhotographyCollection::factory()->create(['active' => true, 'slug' => 'active-one']);
    PhotographyCollection::factory()->create(['active' => false, 'slug' => 'inactive-one']);

    $response = $this->getJson('/api/collections');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.slug'))->toBe('active-one');
});

it('returns collection detail with active photographies in order', function () {
    $camera = Camera::factory()->create();

    $collection = PhotographyCollection::factory()->create([
        'active' => true,
        'slug' => 'my-collection',
    ]);

    $activeFirst = Photography::factory()->create(['active' => true, 'slug' => 'photo-b', 'camera_id' => $camera->id]);
    $activeSecond = Photography::factory()->create(['active' => true, 'slug' => 'photo-a', 'camera_id' => $camera->id]);
    $inactive = Photography::factory()->create(['active' => false, 'slug' => 'photo-off', 'camera_id' => $camera->id]);

    $collection->photographies()->attach($activeFirst, ['position' => 2]);
    $collection->photographies()->attach($activeSecond, ['position' => 1]);
    $collection->photographies()->attach($inactive, ['position' => 3]);

    $response = $this->getJson('/api/collection/my-collection');

    $response->assertOk();
    expect($response->json('data.slug'))->toBe('my-collection');
    expect($response->json('data.photographies'))->toHaveCount(2);
    expect(collect($response->json('data.photographies'))->pluck('slug')->all())->toBe(['photo-a', 'photo-b']);
});

it('orders photographies by pivot position in api response', function () {
    $camera = Camera::factory()->create();
    $collection = PhotographyCollection::factory()->create(['active' => true, 'slug' => 'ordered']);

    $first = Photography::factory()->create(['name' => 'First', 'slug' => 'first', 'camera_id' => $camera->id, 'active' => true]);
    $second = Photography::factory()->create(['name' => 'Second', 'slug' => 'second', 'camera_id' => $camera->id, 'active' => true]);

    $collection->photographies()->attach($first, ['position' => 2]);
    $collection->photographies()->attach($second, ['position' => 1]);

    $response = $this->getJson('/api/collection/ordered');

    expect(collect($response->json('data.photographies'))->pluck('slug')->all())->toBe(['second', 'first']);
});

it('returns 404 for inactive collection', function () {
    PhotographyCollection::factory()->create([
        'active' => false,
        'slug' => 'hidden',
    ]);

    $this->getJson('/api/collection/hidden')->assertNotFound();
});
