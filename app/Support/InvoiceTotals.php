<?php

namespace App\Support;

final class InvoiceTotals
{
    public function __construct(
        public float $subtotal,
        public float $vatRate,
        public float $vatAmount,
        public float $total,
    ) {}

    /**
     * @param  iterable<mixed>  $lines
     */
    public static function fromLines(iterable $lines, float|int|string|null $vatRate): self
    {
        $subtotal = 0.0;

        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $subtotal += self::lineTotal($line['unit_price'] ?? 0, $line['quantity'] ?? 0);
        }

        $subtotal = round($subtotal, 2);
        $rate = round((float) $vatRate, 2);
        $vatAmount = round($subtotal * $rate / 100, 2);

        return new self($subtotal, $rate, $vatAmount, round($subtotal + $vatAmount, 2));
    }

    public static function lineTotal(mixed $unitPrice, mixed $quantity): float
    {
        return round((float) $unitPrice * (float) $quantity, 2);
    }

    public static function formatMoney(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);
        $isWhole = abs($amount - round($amount)) < 0.001;
        $formatted = number_format($amount, $isWhole ? 0 : 2, ',', "\u{00A0}");

        return $formatted."\u{00A0}€";
    }

    public static function formatQuantity(float|int|string|null $quantity): string
    {
        $quantity = round((float) $quantity, 2);

        if (abs($quantity - round($quantity)) < 0.001) {
            $whole = (int) round($quantity);

            return $whole >= 0 && $whole < 100
                ? str_pad((string) $whole, 2, '0', STR_PAD_LEFT)
                : (string) $whole;
        }

        return number_format($quantity, 2, ',', "\u{00A0}");
    }

    public static function formatRate(float|int|string|null $rate): string
    {
        $rate = round((float) $rate, 2);

        if (abs($rate - round($rate)) < 0.001) {
            return (string) (int) round($rate);
        }

        return number_format($rate, 2, ',', "\u{00A0}");
    }
}
