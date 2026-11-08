<?php

use App\Models\InvoiceLine;

it('formats the unit price, quantity and line total', function () {
    $line = new InvoiceLine([
        'description' => 'Carte de visite',
        'unit_price' => 300,
        'quantity' => 3,
        'position' => 1,
    ]);

    expect($line->lineTotal())->toBe(900.0)
        ->and($line->formattedUnitPrice())->toBe("300\u{00A0}€")
        ->and($line->formattedQuantity())->toBe('03')
        ->and($line->formattedLineTotal())->toBe("900\u{00A0}€");
});

it('belongs to an invoice', function () {
    expect(method_exists(InvoiceLine::class, 'invoice'))->toBeTrue();
});
