<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Support\InvoiceTotals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use LogicException;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'number',
        'issued_on',
        'status',
        'sent_at',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'vat_rate',
        ...BillingSetting::ISSUER_FIELDS,
    ];

    protected function casts(): array
    {
        return [
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'issued_on' => 'date',
            'sent_at' => 'datetime',
            'vat_rate' => 'decimal:2',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('position');
    }

    public static function nextNumber(InvoiceType $type, ?int $year = null): string
    {
        $year ??= (int) now()->year;
        $prefix = $type->prefix().'-'.$year.'-';

        $max = static::query()
            ->where('number', 'like', $prefix.'%')
            ->pluck('number')
            ->map(function (string $number) use ($prefix): int {
                $suffix = substr($number, strlen($prefix));

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        return $prefix.str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    public function totals(): InvoiceTotals
    {
        $this->loadMissing('lines');

        return InvoiceTotals::fromLines(
            $this->lines->map(fn (InvoiceLine $line): array => [
                'unit_price' => $line->unit_price,
                'quantity' => $line->quantity,
            ])->all(),
            $this->vat_rate,
        );
    }

    public function formattedSubtotal(): string
    {
        return InvoiceTotals::formatMoney($this->totals()->subtotal);
    }

    public function formattedVatAmount(): string
    {
        return InvoiceTotals::formatMoney($this->totals()->vatAmount);
    }

    public function formattedTotal(): string
    {
        return InvoiceTotals::formatMoney($this->totals()->total);
    }

    public function formattedVatRate(): string
    {
        return InvoiceTotals::formatRate($this->vat_rate);
    }

    public function formattedIssuedOn(): string
    {
        return $this->issued_on->format('d/m/y');
    }

    public function numberLabel(): string
    {
        return $this->type->getLabel().' n°'.$this->number;
    }

    public function paymentOrderName(): string
    {
        return $this->payment_order ?: ($this->issuer_name ?? '');
    }

    public function filename(): string
    {
        $prefix = $this->type === InvoiceType::Quote ? 'devis' : 'facture';
        $safeNumber = preg_replace('/[^A-Za-z0-9._-]+/', '-', $this->number) ?: 'document';

        return $prefix.'-'.$safeNumber.'.pdf';
    }

    public function defaultMailSubject(): string
    {
        return $this->numberLabel();
    }

    public function defaultMailBody(): string
    {
        $signature = trim((string) $this->issuer_name);

        return trim(<<<TEXT
Bonjour,

Veuillez trouver ci-joint {$this->type->getLabel()} n°{$this->number} d'un montant de {$this->formattedTotal()}.

Cordialement
{$signature}
TEXT);
    }

    public function convertToInvoice(): self
    {
        if ($this->type !== InvoiceType::Quote) {
            throw new LogicException('Seuls les devis peuvent être convertis en facture.');
        }

        return DB::transaction(function (): self {
            $invoice = $this->replicate();
            $invoice->type = InvoiceType::Invoice;
            $invoice->status = InvoiceStatus::Draft;
            $invoice->sent_at = null;
            $invoice->number = self::nextNumber(InvoiceType::Invoice);
            $invoice->issued_on = now()->toDateString();
            $invoice->save();

            foreach ($this->lines as $line) {
                $copy = $line->replicate();
                $copy->invoice_id = $invoice->getKey();
                $copy->save();
            }

            return $invoice->load('lines');
        });
    }
}
