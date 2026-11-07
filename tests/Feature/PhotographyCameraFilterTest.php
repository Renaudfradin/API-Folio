<?php

use App\Filament\Resources\Photographies\Pages\ListPhotographies;
use App\Models\Camera;
use App\Models\Photography;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());
});

it('filters photographies by camera', function () {
    $minolta = Camera::factory()->create(['name' => 'Minolta x700']);
    $leica = Camera::factory()->create(['name' => 'Leica M6']);

    $montmartre = Photography::factory()->create([
        'name' => 'Montmartre 30s 1',
        'camera_id' => $minolta->id,
    ]);
    $vincennes = Photography::factory()->create([
        'name' => 'Vincennes parc 1',
        'camera_id' => $leica->id,
    ]);

    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query;
    });

    Livewire::test(ListPhotographies::class)
        ->assertTableFilterExists('camera_id')
        ->assertCanSeeTableRecords([$montmartre, $vincennes])
        ->filterTable('camera_id', $minolta->id)
        ->assertCanSeeTableRecords([$montmartre])
        ->assertCanNotSeeTableRecords([$vincennes]);

    $filterQuery = collect($queries)->first(
        fn ($query) => str_contains($query->sql, 'camera_id'),
    );

    expect($filterQuery)->not->toBeNull()
        ->and($filterQuery->sql)->not->toContain('cameras')
        ->and($filterQuery->bindings)->toContain((string) $minolta->id);
});
