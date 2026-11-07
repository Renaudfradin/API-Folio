<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'type' => InvoiceType::Invoice,
            'number' => 'DOC-'.fake()->unique()->numerify('####'),
            'issued_on' => now()->toDateString(),
            'status' => InvoiceStatus::Draft,
            'client_name' => fake()->company(),
            'client_email' => fake()->safeEmail(),
            'client_phone' => '123-456-7890',
            'client_address' => 'Anywhere St., Any City',
            'vat_rate' => 20,
            'issuer_name' => 'Renaud Fradin',
            'issuer_phone' => '123-456-7890',
            'issuer_email' => 'hello@example.com',
            'issuer_website' => 'example.com',
            'issuer_address' => '123 Anywhere St., Any City',
            'payment_order' => 'Renaud Fradin',
            'account_number' => '0123 4567 8901 2345',
            'payment_terms' => 'Paiement sous 30 jours',
            'thank_you' => 'MERCI DE VOTRE CONFIANCE',
        ];
    }

    public function quote(): static
    {
        return $this->state(fn (): array => [
            'type' => InvoiceType::Quote,
        ]);
    }
}
