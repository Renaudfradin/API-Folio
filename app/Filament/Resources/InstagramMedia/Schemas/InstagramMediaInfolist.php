<?php

namespace App\Filament\Resources\InstagramMedia\Schemas;

use App\Models\InstagramMedia;
use App\Traits\HasRoleBasedVisibility;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;

use function Filament\Support\generate_icon_html;

class InstagramMediaInfolist
{
    use HasRoleBasedVisibility;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Aperçu')
                    ->schema([
                        TextEntry::make('caption')
                            ->label('Légende')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Section::make('Médias')
                    ->description('Cadre 4:5 dans la section — flèches gauche/droite, plein écran (F).')
                    ->schema([
                        ViewEntry::make('media_slider')
                            ->hiddenLabel()
                            ->view('filament.infolists.instagram-media-slider')
                            ->viewData(fn (InstagramMedia $record): array => [
                                'items' => collect($record->all_media_items)
                                    ->map(fn (array $item): array => [
                                        'preview' => $item['thumbnail_url'] ?? $item['media_url'] ?? null,
                                        'media_url' => $item['media_url'] ?? null,
                                        'media_type' => $item['media_type'] ?? null,
                                    ])
                                    ->filter(fn (array $item): bool => filled($item['preview']) || filled($item['media_url']))
                                    ->values()
                                    ->all(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Commentaires')
                    ->description('Consultez et répondez aux commentaires Instagram (style fil de discussion).')
                    ->visible(fn (InstagramMedia $record): bool => ! $record->isStory() && ! self::isCurrentUserDemo())
                    ->schema([
                        ViewEntry::make('comments_panel')
                            ->hiddenLabel()
                            ->view('filament.infolists.instagram-media-comments')
                            ->viewData(fn (InstagramMedia $record): array => [
                                'recordId' => $record->id,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Publication')
                    ->schema([
                        TextEntry::make('account.username')
                            ->label('Compte'),
                        TextEntry::make('media_type')
                            ->label('Type')
                            ->badge(),
                        TextEntry::make('media_product_type')
                            ->label('Produit')
                            ->placeholder('-')
                            ->badge(),
                        TextEntry::make('timestamp')
                            ->label('Publié le')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('media_id')
                            ->label('ID média Instagram')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('permalink')
                            ->label('Lien public')
                            ->placeholder('-')
                            ->url(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('Performances')
                    ->visible(fn (InstagramMedia $record): bool => ! $record->isStory())
                    ->description('Les « — » = métrique absente de l’API (resynchronisez le compte, permission instagram_business_manage_insights).')
                    ->schema([
                        self::metricEntry(TextEntry::make('engagement_rate'), 'heroicon-o-presentation-chart-line', 'Taux d’eng.')
                            ->formatStateUsing(fn (?float $state): string => $state !== null ? number_format($state, 0, ',', ' ').' %' : '—'),
                        self::metricEntry(TextEntry::make('view_count'), 'heroicon-o-eye', 'Vues')
                            ->state(fn (InstagramMedia $record): ?int => $record->hasSyncedViewCount() ? (int) $record->view_count : null)
                            ->numeric()
                            ->placeholder('—'),
                        self::metricEntry(TextEntry::make('insights_shares'), 'heroicon-o-share', 'Partages')
                            ->state(fn (InstagramMedia $record): ?int => $record->insightValue('shares'))
                            ->numeric()
                            ->placeholder('—'),
                        self::metricEntry(TextEntry::make('insights_saved'), 'heroicon-o-bookmark', 'Enregistrements')
                            ->state(fn (InstagramMedia $record): ?int => $record->insightValue('saved'))
                            ->numeric()
                            ->placeholder('—'),
                        self::metricEntry(TextEntry::make('insights_follows'), 'heroicon-o-arrow-trending-up', 'Abonnements')
                            ->state(fn (InstagramMedia $record): ?int => $record->insightValue('follows'))
                            ->numeric()
                            ->placeholder('—'),
                        self::metricEntry(TextEntry::make('insights_reach'), 'heroicon-o-arrow-up', 'Portée')
                            ->state(fn (InstagramMedia $record): ?int => $record->insightValue('reach'))
                            ->numeric()
                            ->placeholder('—'),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),
                Section::make('Statistiques story')
                    ->visible(fn (InstagramMedia $record): bool => $record->isStory())
                    ->schema([
                        self::metricEntry(TextEntry::make('view_count'), 'heroicon-o-eye', 'Vues')
                            ->numeric(),
                        self::metricEntry(TextEntry::make('insights_reach'), 'heroicon-o-arrow-up', 'Portée')
                            ->state(fn (InstagramMedia $record): int => $record->insightCount('reach'))
                            ->numeric(),
                        self::metricEntry(TextEntry::make('story_replies'), 'heroicon-o-chat-bubble-left-right', 'Réponses')
                            ->state(fn (InstagramMedia $record): int => $record->storyRepliesCount())
                            ->numeric(),
                        self::metricEntry(TextEntry::make('story_reactions'), 'heroicon-o-heart', 'Réactions')
                            ->state(fn (InstagramMedia $record): int => $record->storyReactionsCount())
                            ->numeric(),
                        self::metricEntry(TextEntry::make('engagement_rate'), 'heroicon-o-presentation-chart-line', 'Taux d’eng.')
                            ->formatStateUsing(fn (?float $state): string => $state !== null ? number_format($state, 0, ',', ' ').' %' : '—'),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
                Section::make('Synchronisation')
                    ->schema([
                        TextEntry::make('synced_at')
                            ->label('Dernière synchro')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected static function metricEntry(TextEntry $entry, string $icon, string $label): TextEntry
    {
        $iconHtml = generate_icon_html($icon, attributes: new ComponentAttributeBag([
            'class' => 'fi-icon',
            'style' => 'display:inline-block;width:1rem;height:1rem;flex-shrink:0;vertical-align:middle;',
        ]));

        return $entry->label(new HtmlString(
            '<span style="display:inline-flex;flex-direction:row;flex-wrap:nowrap;align-items:center;gap:0.375rem;">'
            .($iconHtml?->toHtml() ?? '')
            .'<span>'.e($label).'</span>'
            .'</span>'
        ));
    }
}
