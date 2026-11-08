<?php

use App\Enums\InvoiceStatus;

it('labels draft and sent invoices', function () {
    expect(InvoiceStatus::Draft->getLabel())->toBe('Brouillon')
        ->and(InvoiceStatus::Sent->getLabel())->toBe('Envoyé')
        ->and(InvoiceStatus::Draft->getColor())->toBe('gray')
        ->and(InvoiceStatus::Sent->getColor())->toBe('success')
        ->and(InvoiceStatus::Draft->value)->toBe('draft')
        ->and(InvoiceStatus::Sent->value)->toBe('sent');
});
