<?php

use App\Support\InvoiceTotals;

it('sums line amounts and applies the vat rate', function () {
    $totals = InvoiceTotals::fromLines([
        ['unit_price' => 10.5, 'quantity' => 2],
        ['unit_price' => '4.25', 'quantity' => '1'],
    ], '5.5');

    expect($totals->subtotal)->toBe(25.25)
        ->and($totals->vatRate)->toBe(5.5)
        ->and($totals->vatAmount)->toBe(1.39)
        ->and($totals->total)->toBe(26.64);
});

it('ignores lines that are not arrays and missing amounts', function () {
    $totals = InvoiceTotals::fromLines([
        'not-a-line',
        ['description' => 'Sans prix'],
        ['unit_price' => 12, 'quantity' => 0],
    ], null);

    expect($totals->subtotal)->toBe(0.0)
        ->and($totals->vatRate)->toBe(0.0)
        ->and($totals->vatAmount)->toBe(0.0)
        ->and($totals->total)->toBe(0.0);
});

it('rounds each line before summing', function () {
    expect(InvoiceTotals::lineTotal(10.333, 2))->toBe(20.67)
        ->and(InvoiceTotals::fromLines([
            ['unit_price' => 10.333, 'quantity' => 2],
            ['unit_price' => 10.333, 'quantity' => 2],
        ], 0)->subtotal)->toBe(41.34);
});

it('formats money with a grouped euro amount', function () {
    expect(InvoiceTotals::formatMoney(4650))->toBe("4\u{00A0}650\u{00A0}€")
        ->and(InvoiceTotals::formatMoney(10.5))->toBe("10,50\u{00A0}€")
        ->and(InvoiceTotals::formatMoney(null))->toBe("0\u{00A0}€");
});

it('formats quantities with a leading zero under one hundred', function () {
    expect(InvoiceTotals::formatQuantity(3))->toBe('03')
        ->and(InvoiceTotals::formatQuantity(0))->toBe('00')
        ->and(InvoiceTotals::formatQuantity(100))->toBe('100')
        ->and(InvoiceTotals::formatQuantity(1.5))->toBe('1,50');
});

it('formats vat rates without trailing zeros', function () {
    expect(InvoiceTotals::formatRate(20))->toBe('20')
        ->and(InvoiceTotals::formatRate(5.5))->toBe('5,50')
        ->and(InvoiceTotals::formatRate(null))->toBe('0');
});
