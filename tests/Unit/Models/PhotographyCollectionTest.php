<?php

use App\Models\Photography;
use App\Models\PhotographyCollection;

it('defines photographies relation method', function () {
    expect(method_exists(PhotographyCollection::class, 'photographies'))->toBeTrue();
});

it('defines active photographies relation method', function () {
    expect(method_exists(PhotographyCollection::class, 'activePhotographies'))->toBeTrue();
});

it('defines active scope method', function () {
    expect(method_exists(PhotographyCollection::class, 'scopeActive'))->toBeTrue();
});

it('has correct fillable fields', function () {
    $collection = new PhotographyCollection;

    expect($collection->getFillable())->toBe([
        'name',
        'slug',
        'description',
        'active',
    ]);
});

it('photography model defines photography collections relation', function () {
    expect(method_exists(Photography::class, 'photographyCollections'))->toBeTrue();
});
