<?php

namespace App\Filament\Billing;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class IssuerFields
{
    /**
     * @return array<int, TextInput|Textarea>
     */
    public static function components(): array
    {
        return [
            TextInput::make('issuer_name')
                ->label('Nom')
                ->maxLength(255),
            TextInput::make('issuer_phone')
                ->label('Téléphone')
                ->tel()
                ->maxLength(255),
            TextInput::make('issuer_email')
                ->label('E-mail')
                ->email()
                ->nullable()
                ->maxLength(255),
            TextInput::make('issuer_website')
                ->label('Site')
                ->maxLength(255),
            Textarea::make('issuer_address')
                ->label('Adresse')
                ->rows(3)
                ->columnSpanFull(),
            TextInput::make('payment_order')
                ->label("Paiement à l'ordre de")
                ->maxLength(255),
            TextInput::make('account_number')
                ->label('N° de compte')
                ->maxLength(255),
            TextInput::make('payment_terms')
                ->label('Conditions de paiement')
                ->maxLength(255),
            TextInput::make('thank_you')
                ->label('Mention de remerciement')
                ->maxLength(255)
                ->columnSpanFull(),
        ];
    }
}
