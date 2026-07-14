<?php

namespace App\Filament\Resources\LinkedInProfileStats;

use App\Filament\Resources\LinkedInProfileStats\Pages\CreateLinkedInProfileStat;
use App\Filament\Resources\LinkedInProfileStats\Pages\EditLinkedInProfileStat;
use App\Filament\Resources\LinkedInProfileStats\Pages\ListLinkedInProfileStats;
use App\Filament\Resources\LinkedInProfileStats\Pages\ViewLinkedInProfileStat;
use App\Filament\Resources\LinkedInProfileStats\Schemas\LinkedInProfileStatForm;
use App\Filament\Resources\LinkedInProfileStats\Schemas\LinkedInProfileStatInfolist;
use App\Filament\Resources\LinkedInProfileStats\Tables\LinkedInProfileStatsTable;
use App\Models\LinkedinProfileStat;
use App\Traits\HasRoleBasedVisibility;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LinkedInProfileStatResource extends Resource
{
    use HasRoleBasedVisibility;

    protected static ?string $model = LinkedinProfileStat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'metric_label';

    protected static string|\UnitEnum|null $navigationGroup = 'LinkedIn';

    public static function canViewAny(): bool
    {
        return self::isCurrentUserAdmin();
    }

    public static function getNavigationLabel(): string
    {
        return __('Stats LinkedIn');
    }

    public static function getModelLabel(): string
    {
        return __('Stat LinkedIn');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Stats LinkedIn');
    }

    public static function form(Schema $schema): Schema
    {
        return LinkedInProfileStatForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LinkedInProfileStatInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LinkedInProfileStatsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLinkedInProfileStats::route('/'),
            'create' => CreateLinkedInProfileStat::route('/create'),
            'view' => ViewLinkedInProfileStat::route('/{record}'),
            'edit' => EditLinkedInProfileStat::route('/{record}/edit'),
        ];
    }
}
