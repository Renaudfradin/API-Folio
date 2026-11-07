<?php

namespace App\Filament\Pages;

use App\Filament\Billing\IssuerFields;
use App\Models\BillingSetting;
use App\Traits\HasRoleBasedVisibility;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class BillingSettings extends Page
{
    use HasRoleBasedVisibility;

    protected static string|UnitEnum|null $navigationGroup = 'Facturation';

    protected static ?string $navigationLabel = 'Émetteur';

    protected static ?string $title = 'Émetteur';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return self::isCurrentUserAdmin();
    }

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(BillingSetting::current()->issuerAttributes());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coordonnées')
                    ->description('Ces informations préremplissent chaque nouveau devis ou facture.')
                    ->columns(2)
                    ->schema(IssuerFields::components()),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Action::make('save')
                            ->label('Enregistrer')
                            ->submit('save'),
                    ]),
            ]);
    }

    public function save(): void
    {
        BillingSetting::current()->update($this->form->getState());

        Notification::make()
            ->title('Coordonnées enregistrées')
            ->success()
            ->send();
    }
}
