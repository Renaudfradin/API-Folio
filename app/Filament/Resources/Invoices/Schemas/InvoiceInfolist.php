<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use App\Support\InvoiceTotals;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (InvoiceType $state): string => $state->getLabel())
                            ->color(fn (InvoiceType $state): string => $state->getColor()),
                        TextEntry::make('number')
                            ->label('Numéro'),
                        TextEntry::make('issued_on')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (InvoiceStatus $state): string => $state->getLabel())
                            ->color(fn (InvoiceStatus $state): string => $state->getColor()),
                    ]),
                Section::make('Client')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('client_name')
                            ->label('Nom'),
                        TextEntry::make('client_email')
                            ->label('E-mail')
                            ->placeholder('—'),
                        TextEntry::make('client_phone')
                            ->label('Téléphone')
                            ->placeholder('—'),
                        TextEntry::make('client_address')
                            ->label('Adresse')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Lignes')
                    ->schema([
                        RepeatableEntry::make('lines')
                            ->label('')
                            ->schema([
                                TextEntry::make('description')
                                    ->label('Description'),
                                TextEntry::make('unit_price')
                                    ->label('Prix')
                                    ->formatStateUsing(fn ($state): string => InvoiceTotals::formatMoney($state)),
                                TextEntry::make('quantity')
                                    ->label('Quantité')
                                    ->formatStateUsing(fn ($state): string => InvoiceTotals::formatQuantity($state)),
                                TextEntry::make('line_total')
                                    ->label('Total')
                                    ->state(fn (Get $get): string => InvoiceTotals::formatMoney(
                                        InvoiceTotals::lineTotal($get('unit_price'), $get('quantity')),
                                    )),
                            ])
                            ->columns(4),
                    ]),
                Section::make('Totaux')
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Sous-total')
                            ->state(fn (Invoice $record): string => $record->formattedSubtotal()),
                        TextEntry::make('vat')
                            ->label(fn (Invoice $record): string => 'TVA ('.$record->formattedVatRate().'%)')
                            ->state(fn (Invoice $record): string => $record->formattedVatAmount()),
                        TextEntry::make('total')
                            ->label('Total')
                            ->state(fn (Invoice $record): string => $record->formattedTotal())
                            ->weight(FontWeight::Bold),
                    ]),
                Section::make('Émetteur')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('issuer_name')->label('Nom')->placeholder('—'),
                        TextEntry::make('issuer_phone')->label('Téléphone')->placeholder('—'),
                        TextEntry::make('issuer_email')->label('E-mail')->placeholder('—'),
                        TextEntry::make('issuer_website')->label('Site')->placeholder('—'),
                        TextEntry::make('issuer_address')->label('Adresse')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('payment_order')->label("Paiement à l'ordre de")->placeholder('—'),
                        TextEntry::make('account_number')->label('N° de compte')->placeholder('—'),
                        TextEntry::make('payment_terms')->label('Conditions de paiement')->placeholder('—'),
                        TextEntry::make('thank_you')->label('Mention')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
