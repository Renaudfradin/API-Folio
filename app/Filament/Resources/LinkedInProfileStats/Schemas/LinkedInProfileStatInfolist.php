<?php

namespace App\Filament\Resources\LinkedInProfileStats\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LinkedInProfileStatInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Utilisateur'),
                TextEntry::make('metric_key')
                    ->label('Clé'),
                TextEntry::make('metric_label')
                    ->label('Libellé'),
                TextEntry::make('value')
                    ->label('Valeur numérique')
                    ->placeholder('-'),
                TextEntry::make('value_text')
                    ->label('Valeur texte')
                    ->placeholder('-'),
                TextEntry::make('period_start')
                    ->label('Début de période')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('period_end')
                    ->label('Fin de période')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('source')
                    ->label('Source'),
                TextEntry::make('synced_at')
                    ->label('Synchronisé le')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('payload')
                    ->label('Payload JSON')
                    ->columnSpanFull()
                    ->placeholder('-'),
            ]);
    }
}
