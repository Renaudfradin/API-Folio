<?php

use App\Filament\Resources\Experiences\Pages\ListExperiences;
use App\Models\Experience;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->admin()->create());
});

it('filters experiences by company', function () {
    $konkrete = Experience::factory()->create([
        'title' => 'Développeur Full Stack',
        'company' => 'Konkrete',
    ]);
    $bearco = Experience::factory()->create([
        'title' => 'Développeur front-end',
        'company' => 'Bearco',
    ]);

    Livewire::test(ListExperiences::class)
        ->assertTableFilterExists('company')
        ->assertCanSeeTableRecords([$konkrete, $bearco])
        ->filterTable('company', 'Konkrete')
        ->assertCanSeeTableRecords([$konkrete])
        ->assertCanNotSeeTableRecords([$bearco]);
});
