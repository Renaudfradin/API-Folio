<?php

use App\Models\BillingSetting;
use Tests\TestCase;

uses(TestCase::class);

it('creates the current billing settings with the default mentions', function () {
    $settings = BillingSetting::current();

    expect($settings->exists)->toBeTrue()
        ->and($settings->payment_terms)->toBe('Paiement sous 30 jours')
        ->and($settings->thank_you)->toBe('MERCI DE VOTRE CONFIANCE')
        ->and(BillingSetting::query()->count())->toBe(1);

    $settings->update(['issuer_name' => 'Renaud Fradin']);

    expect(BillingSetting::current()->is($settings))->toBeTrue()
        ->and(BillingSetting::current()->issuer_name)->toBe('Renaud Fradin');
});

it('returns only the issuer attributes copied onto a document', function () {
    $settings = BillingSetting::current();
    $settings->update([
        'issuer_name' => 'Renaud Fradin',
        'issuer_email' => 'hello@example.com',
        'payment_terms' => 'Paiement sous 30 jours',
    ]);

    expect($settings->issuerAttributes())->toBe([
        'issuer_name' => 'Renaud Fradin',
        'issuer_phone' => null,
        'issuer_email' => 'hello@example.com',
        'issuer_website' => null,
        'issuer_address' => null,
        'payment_order' => null,
        'account_number' => null,
        'payment_terms' => 'Paiement sous 30 jours',
        'thank_you' => 'MERCI DE VOTRE CONFIANCE',
    ]);
});
