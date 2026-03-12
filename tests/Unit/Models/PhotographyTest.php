<?php

use App\Models\Photography;

it('defines camera relation method', function () {
    expect(method_exists(Photography::class, 'camera'))->toBeTrue();
});

it('defines documents relation method', function () {
    expect(method_exists(Photography::class, 'documents'))->toBeTrue();
});

it('defines active scope method', function () {
    expect(method_exists(Photography::class, 'scopeActive'))->toBeTrue();
});

it('has correct date casting', function () {
    $photography = new Photography();

    expect($photography->getCasts())->toHaveKey('date', 'datetime');
});

it('has correct fillable fields', function () {
    $photography = new Photography();

    $expectedFillable = [
        'name',
        'slug',
        'image',
        'date',
        'series',
        'city',
        'camera_id',
        'active',
    ];

    expect($photography->getFillable())->toBe($expectedFillable);
});
