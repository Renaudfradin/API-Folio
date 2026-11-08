<?php

namespace App\Models;

use App\Support\InvoiceTotals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'unit_price',
        'quantity',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'decimal:2',
            'position' => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function lineTotal(): float
    {
        return InvoiceTotals::lineTotal($this->unit_price, $this->quantity);
    }

    public function formattedUnitPrice(): string
    {
        return InvoiceTotals::formatMoney($this->unit_price);
    }

    public function formattedQuantity(): string
    {
        return InvoiceTotals::formatQuantity($this->quantity);
    }

    public function formattedLineTotal(): string
    {
        return InvoiceTotals::formatMoney($this->lineTotal());
    }
}
