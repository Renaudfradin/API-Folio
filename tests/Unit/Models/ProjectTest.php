<?php

use App\Models\Project;

it('has fillable fields', function () {
    $project = new Project([
        'name' => 'React Dashboard',
        'slug' => 'react-dashboard',
        'description' => 'Admin dashboard with React',
        'url' => 'https://react-dashboard.com',
        'url_github' => 'https://github.com/user/react-dashboard',
        'stack' => ['React', 'Node.js', 'MongoDB'],
        'active' => false,
    ]);

    expect($project->name)->toBe('React Dashboard')
        ->and($project->description)->toBe('Admin dashboard with React')
        ->and($project->stack)->toBe(['React', 'Node.js', 'MongoDB'])
        ->and($project->active)->toBeFalse();
});

it('has correct morph type constant', function () {
    expect(Project::MORPH_TYPE)->toBe('Project');
});

it('defines documents relation method', function () {
    expect(method_exists(Project::class, 'documents'))->toBeTrue();
});

it('defines active scope method', function () {
    expect(method_exists(Project::class, 'scopeActive'))->toBeTrue();
});

it('has correct stack casting', function () {
    $project = new Project();

    expect($project->getCasts())->toHaveKey('stack', 'array');
});

it('has correct fillable fields', function () {
    $project = new Project();

    $expectedFillable = [
        'name',
        'slug',
        'description',
        'url',
        'url_github',
        'stack',
        'active',
    ];

    expect($project->getFillable())->toBe($expectedFillable);
});
