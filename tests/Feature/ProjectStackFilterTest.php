<?php

use App\Enums\Stack;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());
});

it('filters projects that include any of the selected stacks', function () {
    $folio = Project::factory()->create([
        'name' => 'API FOLIO',
        'stack' => [Stack::Laravel->value, Stack::PHP->value],
    ]);
    $map = Project::factory()->create([
        'name' => 'Earth map game',
        'stack' => [Stack::React->value, Stack::Next->value, Stack::ThreeJS->value],
    ]);
    $studio = Project::factory()->create([
        'name' => 'AththStudio',
        'stack' => [Stack::Next->value],
    ]);
    $portfolio = Project::factory()->create([
        'name' => 'PORTFOLIO V2',
        'stack' => [Stack::Vue->value],
    ]);

    Livewire::test(ListProjects::class)
        ->assertTableFilterExists('stack')
        ->assertCanSeeTableRecords([$folio, $map, $studio, $portfolio])
        ->filterTable('stack', [Stack::Next->value, Stack::Laravel->value])
        ->assertCanSeeTableRecords([$folio, $map, $studio])
        ->assertCanNotSeeTableRecords([$portfolio]);
});
