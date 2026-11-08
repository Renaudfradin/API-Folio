<?php

use App\Enums\InvoiceType;

it('labels quotes and invoices', function () {
    expect(InvoiceType::Quote->getLabel())->toBe('Devis')
        ->and(InvoiceType::Invoice->getLabel())->toBe('Facture')
        ->and(InvoiceType::Quote->documentTitle())->toBe('DEVIS')
        ->and(InvoiceType::Invoice->documentTitle())->toBe('FACTURE');
});

it('uses separate number prefixes and colors', function () {
    expect(InvoiceType::Quote->prefix())->toBe('DEV')
        ->and(InvoiceType::Invoice->prefix())->toBe('FAC')
        ->and(InvoiceType::Quote->getColor())->toBe('info')
        ->and(InvoiceType::Invoice->getColor())->toBe('success')
        ->and(InvoiceType::Quote->value)->toBe('quote')
        ->and(InvoiceType::Invoice->value)->toBe('invoice');
});
