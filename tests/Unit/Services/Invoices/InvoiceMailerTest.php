<?php

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Services\Invoices\InvoiceMailer;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(TestCase::class);

it('sends the invoice and marks it as sent', function () {
    Mail::fake();

    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatus::Draft,
        'sent_at' => null,
    ]);

    app(InvoiceMailer::class)->send(
        $invoice,
        'client@example.com',
        'Votre facture',
        'Bonjour, voici la facture.',
    );

    Mail::assertSent(InvoiceMail::class, function (InvoiceMail $mail) use ($invoice): bool {
        return $mail->hasTo('client@example.com')
            && $mail->invoice->is($invoice)
            && $mail->mailSubject === 'Votre facture'
            && $mail->messageBody === 'Bonjour, voici la facture.';
    });

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Sent)
        ->and($invoice->sent_at)->not->toBeNull();
});
