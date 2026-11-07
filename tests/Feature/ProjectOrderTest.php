<?php

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('assigns the next position when a project is created', function () {
    $first = Project::factory()->create();
    $second = Project::factory()->create(['position' => 8]);
    $third = Project::factory()->create();

    expect($first->position)->toBe(1)
        ->and($second->position)->toBe(8)
        ->and($third->position)->toBe(9);
});

it('lets an admin reorder projects from the list', function () {
    $this->actingAs(User::factory()->admin()->create());

    $first = Project::factory()->create(['name' => 'Premier', 'position' => 1]);
    $second = Project::factory()->create(['name' => 'Second', 'position' => 2]);

    Livewire::test(ListProjects::class)
        ->assertSee('Changer l’ordre')
        ->call('reorderTable', [(string) $second->getKey(), (string) $first->getKey()]);

    expect($second->refresh()->position)->toBe(1)
        ->and($first->refresh()->position)->toBe(2);
});

it('hides reordering from users who are not admins', function () {
    $this->actingAs(User::factory()->create());

    $project = Project::factory()->create(['position' => 4]);

    Livewire::test(ListProjects::class)
        ->assertDontSee('Changer l’ordre')
        ->call('reorderTable', [(string) $project->getKey()]);

    expect($project->refresh()->position)->toBe(4);
});
