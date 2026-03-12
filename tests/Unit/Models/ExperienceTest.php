<?php

use App\Models\Experience;

it('defines active scope method', function () {
    expect(method_exists(Experience::class, 'scopeActive'))->toBeTrue();
});

it('has correct date casting for start_date', function () {
    $experience = new Experience();

    expect($experience->getCasts())->toHaveKey('start_date', 'datetime');
});

it('has correct date casting for end_date', function () {
    $experience = new Experience();

    expect($experience->getCasts())->toHaveKey('end_date', 'datetime');
});

it('has correct fillable fields', function () {
    $experience = new Experience();

    $expectedFillable = [
        'title',
        'slug',
        'company',
        'description',
        'start_date',
        'end_date',
        'type',
        'active',
    ];

    expect($experience->getFillable())->toBe($expectedFillable);
});
