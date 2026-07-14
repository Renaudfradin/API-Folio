<?php

namespace App\Filament\Widgets;

use App\Models\Block;
use App\Models\Experience;
use App\Models\LinkedinConnection;
use App\Models\LinkedinProfileStat;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LinkedInSyncOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $connection = Auth::user()?->linkedinConnection;

        return [
            Stat::make('Connexion', $connection ? 'Connectée' : 'Non connectée'),
            Stat::make('Connexions LinkedIn', LinkedinConnection::query()->count()),
            Stat::make('Dernière synchro', $connection?->last_synced_at?->diffForHumans() ?? '-'),
            Stat::make('Expériences LinkedIn', Experience::query()->where('source', 'linkedin')->count()),
            Stat::make('Projets LinkedIn', Project::query()->where('source', 'linkedin')->count()),
            Stat::make('Sélections LinkedIn', Block::query()->where('source', 'linkedin')->count()),
            Stat::make('Stats profil', LinkedinProfileStat::query()->count()),
        ];
    }
}
