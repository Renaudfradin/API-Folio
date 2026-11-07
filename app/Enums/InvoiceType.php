<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InvoiceType: string implements HasColor, HasLabel
{
    case Quote = 'quote';
    case Invoice = 'invoice';

    public function getLabel(): string
    {
        return match ($this) {
            self::Quote => 'Devis',
            self::Invoice => 'Facture',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Quote => 'info',
            self::Invoice => 'success',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::Quote => 'DEV',
            self::Invoice => 'FAC',
        };
    }

    public function documentTitle(): string
    {
        return match ($this) {
            self::Quote => 'DEVIS',
            self::Invoice => 'FACTURE',
        };
    }
}
