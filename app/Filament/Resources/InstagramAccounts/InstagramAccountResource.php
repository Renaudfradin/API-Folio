<?php

namespace App\Filament\Resources\InstagramAccounts;

use App\Filament\Resources\InstagramAccounts\Pages\CreateInstagramAccount;
use App\Filament\Resources\InstagramAccounts\Pages\EditInstagramAccount;
use App\Filament\Resources\InstagramAccounts\Pages\ListInstagramAccounts;
use App\Filament\Resources\InstagramAccounts\Pages\ViewInstagramAccount;
use App\Filament\Resources\InstagramAccounts\Schemas\InstagramAccountForm;
use App\Filament\Resources\InstagramAccounts\Schemas\InstagramAccountInfolist;
use App\Filament\Resources\InstagramAccounts\Tables\InstagramAccountsTable;
use App\Models\InstagramAccount;
use App\Traits\HasRoleBasedVisibility;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InstagramAccountResource extends Resource
{
    use HasRoleBasedVisibility;

    protected static ?string $model = InstagramAccount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Social';

    protected static ?string $recordTitleAttribute = 'username';

    public static function canViewAny(): bool
    {
        return self::isCurrentUserAdmin();
    }

    public static function getNavigationLabel(): string
    {
        return 'Instagram';
    }

    public static function getModelLabel(): string
    {
        return 'Compte Instagram';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Comptes Instagram';
    }

    public static function form(Schema $schema): Schema
    {
        return InstagramAccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstagramAccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstagramAccountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstagramAccounts::route('/'),
            'create' => CreateInstagramAccount::route('/create'),
            'view' => ViewInstagramAccount::route('/{record}'),
            'edit' => EditInstagramAccount::route('/{record}/edit'),
        ];
    }
}
