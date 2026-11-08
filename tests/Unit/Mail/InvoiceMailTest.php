<?php

use App\Mail\InvoiceMail;
use App\Models\Invoice;

it('uses the given subject and the invoice message view', function () {
    $mail = new InvoiceMail(new Invoice, 'Bonjour, voici la facture.', 'Votre facture');

    expect($mail->envelope()->subject)->toBe('Votre facture')
        ->and($mail->content()->view)->toBe('mail.invoices.message')
        ->and($mail->messageBody)->toBe('Bonjour, voici la facture.');
});
