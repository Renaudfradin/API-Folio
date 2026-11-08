<?php

use App\Models\Invoice;
use App\Services\Invoices\InvoicePdf;
use Tests\TestCase;

uses(TestCase::class);

it('renders the invoice view as a pdf', function () {
    $invoice = Invoice::factory()->create([
        'client_name' => 'Concordia',
    ]);

    $invoice->lines()->create([
        'description' => 'Création de logo',
        'unit_price' => 900,
        'quantity' => 1,
        'position' => 1,
    ]);

    expect(app(InvoicePdf::class)->output($invoice->fresh()))->toStartWith('%PDF');
});
