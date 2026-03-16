<?php

use App\Models\Camera;

it('has fillable fields', function () {
    $camera = new Camera([
        'name' => 'Nikon Z9',
        'slug' => 'nikon-z9',
        'content' => 'Professional mirrorless camera',
        'serie' => 'professional',
        'active' => false,
    ]);

    expect($camera->name)->toBe('Nikon Z9')
        ->and($camera->slug)->toBe('nikon-z9')
        ->and($camera->content)->toBe('Professional mirrorless camera')
        ->and($camera->serie)->toBe('professional')
        ->and($camera->active)->toBeFalse();
});

it('has correct morph type constant', function () {
    expect(Camera::MORPH_TYPE)->toBe('Camera');
});

it('defines photographs relation method', function () {
    expect(method_exists(Camera::class, 'photographs'))->toBeTrue();
});

it('defines documents relation method', function () {
    expect(method_exists(Camera::class, 'documents'))->toBeTrue();
});

it('defines active scope method', function () {
    expect(method_exists(Camera::class, 'scopeActive'))->toBeTrue();
});
