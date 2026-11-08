<?php

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use Tests\TestCase;

uses(TestCase::class);

it('exposes billing casts and fillable issuer fields', function () {
    $invoice = new Invoice;

    expect($invoice->getCasts())
        ->toHaveKey('type', InvoiceType::class)
        ->toHaveKey('status', InvoiceStatus::class)
        ->toHaveKey('issued_on', 'date')
        ->toHaveKey('sent_at', 'datetime')
        ->toHaveKey('vat_rate', 'decimal:2')
        ->and($invoice->getFillable())->toContain(
            'type',
            'number',
            'client_name',
            'vat_rate',
            'issuer_name',
            'payment_terms',
            'thank_you',
        );
});

it('builds labels, filenames and the default email', function () {
    $invoice = new Invoice([
        'type' => InvoiceType::Quote,
        'number' => 'DEV 2026/0001',
        'issued_on' => '2026-01-06',
        'vat_rate' => 20,
        'issuer_name' => 'Renaud Fradin',
        'payment_order' => '',
    ]);

    expect($invoice->numberLabel())->toBe('Devis n°DEV 2026/0001')
        ->and($invoice->filename())->toBe('devis-DEV-2026-0001.pdf')
        ->and($invoice->formattedIssuedOn())->toBe('06/01/26')
        ->and($invoice->formattedVatRate())->toBe('20')
        ->and($invoice->paymentOrderName())->toBe('Renaud Fradin')
        ->and($invoice->defaultMailSubject())->toBe('Devis n°DEV 2026/0001')
        ->and($invoice->defaultMailBody())->toContain('Devis n°DEV 2026/0001')
        ->and($invoice->defaultMailBody())->toContain('Renaud Fradin');
});

it('falls back to a safe filename and the payment order name', function () {
    $invoice = new Invoice([
        'type' => InvoiceType::Invoice,
        'number' => '///',
        'payment_order' => 'Studio Concordia',
        'issuer_name' => 'Renaud Fradin',
    ]);

    expect($invoice->filename())->toBe('facture--.pdf')
        ->and($invoice->paymentOrderName())->toBe('Studio Concordia');

    $invoice->number = '';

    expect($invoice->filename())->toBe('facture-document.pdf');
});

it('numbers each document type and year on its own sequence', function () {
    $year = 2026;

    Invoice::factory()->create([
        'type' => InvoiceType::Invoice,
        'number' => "FAC-{$year}-0009",
    ]);
    Invoice::factory()->create([
        'type' => InvoiceType::Invoice,
        'number' => "FAC-{$year}-brouillon",
    ]);
    Invoice::factory()->create([
        'type' => InvoiceType::Quote,
        'number' => "DEV-{$year}-0003",
    ]);

    expect(Invoice::nextNumber(InvoiceType::Invoice, $year))->toBe("FAC-{$year}-0010")
        ->and(Invoice::nextNumber(InvoiceType::Quote, $year))->toBe("DEV-{$year}-0004")
        ->and(Invoice::nextNumber(InvoiceType::Invoice, $year + 1))->toBe('FAC-'.($year + 1).'-0001');
});

it('totals ordered lines', function () {
    $invoice = Invoice::factory()->create(['vat_rate' => 10]);

    $invoice->lines()->create([
        'description' => 'Deuxième',
        'unit_price' => 50,
        'quantity' => 2,
        'position' => 2,
    ]);
    $invoice->lines()->create([
        'description' => 'Première',
        'unit_price' => 100,
        'quantity' => 1,
        'position' => 1,
    ]);

    $invoice = $invoice->fresh('lines');

    expect($invoice->lines->pluck('description')->all())->toBe(['Première', 'Deuxième'])
        ->and($invoice->totals()->subtotal)->toBe(200.0)
        ->and($invoice->totals()->vatAmount)->toBe(20.0)
        ->and($invoice->formattedTotal())->toBe("220\u{00A0}€");
});

it('converts a sent quote into a draft invoice and keeps the quote', function () {
    $quote = Invoice::factory()->quote()->create([
        'number' => 'DEV-2026-0008',
        'status' => InvoiceStatus::Sent,
        'sent_at' => now(),
        'client_name' => 'Concordia',
        'issued_on' => '2026-01-06',
    ]);

    $quote->lines()->create([
        'description' => 'Création de logo',
        'unit_price' => 900,
        'quantity' => 1,
        'position' => 1,
    ]);

    $invoice = $quote->convertToInvoice();

    expect($invoice->type)->toBe(InvoiceType::Invoice)
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->sent_at)->toBeNull()
        ->and($invoice->number)->toBe('FAC-'.now()->year.'-0001')
        ->and($invoice->issued_on->toDateString())->toBe(now()->toDateString())
        ->and($invoice->client_name)->toBe('Concordia')
        ->and($invoice->lines)->toHaveCount(1)
        ->and($invoice->lines->first()->description)->toBe('Création de logo')
        ->and($quote->fresh()->type)->toBe(InvoiceType::Quote)
        ->and($quote->fresh()->status)->toBe(InvoiceStatus::Sent)
        ->and(Invoice::query()->count())->toBe(2);
});

it('refuses to convert an invoice', function () {
    $invoice = Invoice::factory()->create();

    expect(fn () => $invoice->convertToInvoice())
        ->toThrow(\LogicException::class, 'Seuls les devis peuvent être convertis en facture.');
});
