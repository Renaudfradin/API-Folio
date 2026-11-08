<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    public const ISSUER_FIELDS = [
        'issuer_name',
        'issuer_phone',
        'issuer_email',
        'issuer_website',
        'issuer_address',
        'payment_order',
        'account_number',
        'payment_terms',
        'thank_you',
    ];

    protected $fillable = self::ISSUER_FIELDS;

    public static function current(): self
    {
        $existing = static::query()->first();

        if ($existing) {
            return $existing;
        }

        return static::query()->create([
            'payment_terms' => 'Paiement sous 30 jours',
            'thank_you' => 'MERCI DE VOTRE CONFIANCE',
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    public function issuerAttributes(): array
    {
        return $this->only(self::ISSUER_FIELDS);
    }
}
