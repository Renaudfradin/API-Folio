<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceType;
use App\Filament\Billing\IssuerFields;
use App\Models\Invoice;
use App\Support\InvoiceTotals;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options(InvoiceType::class)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, InvoiceType|string|null $state): void {
                                $type = $state instanceof InvoiceType ? $state : (is_string($state) && $state !== '' ? InvoiceType::tryFrom($state) : null);

                                if (! $type instanceof InvoiceType) {
                                    return;
                                }

                                $number = (string) ($get('number') ?? '');
                                $matchesGeneratedNumber = preg_match('/^(DEV|FAC)-\d{4}-\d+$/', $number) === 1;

                                if ($number === '' || ($matchesGeneratedNumber && ! str_starts_with($number, $type->prefix().'-'))) {
                                    $set('number', Invoice::nextNumber($type));
                                }
                            }),
                        TextInput::make('number')
                            ->label('Numéro')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('issued_on')
                            ->label('Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        TextInput::make('vat_rate')
                            ->label('TVA')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->live(onBlur: true),
                    ]),
                Section::make('Client')
                    ->columns(2)
                    ->schema([
                        TextInput::make('client_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('client_email')
                            ->label('E-mail')
                            ->email()
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('client_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('client_address')
                            ->label('Adresse')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Lignes')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('lines')
                            ->hiddenLabel()
                            ->relationship()
                            ->orderColumn('position')
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('Ajouter une ligne')
                            ->itemLabel(fn (array $state): ?string => filled($state['description'] ?? null) ? (string) $state['description'] : null)
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('description')
                                        ->label('Description')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2),
                                    TextInput::make('unit_price')
                                        ->label('Prix')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->suffix('€')
                                        ->live(onBlur: true),
                                    TextInput::make('quantity')
                                        ->label('Quantité')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->default(1)
                                        ->live(onBlur: true),
                                    Text::make(function (Get $get): string {
                                        return 'Total : '.InvoiceTotals::formatMoney(
                                            InvoiceTotals::lineTotal($get('unit_price'), $get('quantity')),
                                        );
                                    })
                                        ->weight(FontWeight::SemiBold)
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->columnSpanFull(),
                        Text::make(fn (Get $get): string => 'Sous-total : '.InvoiceTotals::formatMoney(self::totals($get)->subtotal))
                            ->columnSpanFull(),
                        Text::make(function (Get $get): string {
                            $totals = self::totals($get);

                            return 'TVA ('.InvoiceTotals::formatRate($totals->vatRate).'%) : '.InvoiceTotals::formatMoney($totals->vatAmount);
                        })->columnSpanFull(),
                        Text::make(fn (Get $get): string => 'Total : '.InvoiceTotals::formatMoney(self::totals($get)->total))
                            ->weight(FontWeight::Bold)
                            ->columnSpanFull(),
                    ]),
                Section::make('Émetteur')
                    ->columnSpanFull()
                    ->description('Repris de la fiche émetteur, et enregistré sur ce document.')
                    ->collapsed()
                    ->columns(2)
                    ->schema(IssuerFields::components()),
            ]);
    }

    private static function totals(Get $get): InvoiceTotals
    {
        return InvoiceTotals::fromLines($get('lines') ?? [], $get('vat_rate'));
    }
}
