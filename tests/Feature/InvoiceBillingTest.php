<?php

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Filament\Pages\BillingSettings;
use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Mail\InvoiceMail;
use App\Models\BillingSetting;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Invoices\InvoiceMailer;
use App\Services\Invoices\InvoicePdf;
use App\Support\InvoiceTotals;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('calculates the sample invoice totals', function () {
    $totals = InvoiceTotals::fromLines([
        ['unit_price' => 900, 'quantity' => 1],
        ['unit_price' => 300, 'quantity' => 1],
        ['unit_price' => 300, 'quantity' => 3],
        ['unit_price' => 200, 'quantity' => 4],
        ['unit_price' => 1500, 'quantity' => 1],
        ['unit_price' => 250, 'quantity' => 1],
    ], 20);

    expect($totals->subtotal)->toBe(4650.0)
        ->and($totals->vatAmount)->toBe(930.0)
        ->and($totals->total)->toBe(5580.0)
        ->and(InvoiceTotals::formatMoney($totals->subtotal))->toBe("4\u{00A0}650\u{00A0}€")
        ->and(InvoiceTotals::formatQuantity(3))->toBe('03');
});

it('numbers quotes and invoices separately', function () {
    $year = now()->year;

    expect(Invoice::nextNumber(InvoiceType::Invoice))->toBe("FAC-{$year}-0001")
        ->and(Invoice::nextNumber(InvoiceType::Quote))->toBe("DEV-{$year}-0001");

    Invoice::factory()->create([
        'type' => InvoiceType::Invoice,
        'number' => "FAC-{$year}-0004",
    ]);

    expect(Invoice::nextNumber(InvoiceType::Invoice))->toBe("FAC-{$year}-0005")
        ->and(Invoice::nextNumber(InvoiceType::Quote))->toBe("DEV-{$year}-0001");
});

it('keeps the quote when converting it into an invoice', function () {
    $quote = Invoice::factory()->quote()->create([
        'number' => 'DEV-'.now()->year.'-0002',
        'vat_rate' => 20,
        'client_name' => 'Concordia',
    ]);

    $quote->lines()->create([
        'description' => 'Création de logo',
        'unit_price' => 900,
        'quantity' => 1,
        'position' => 1,
    ]);

    $invoice = $quote->convertToInvoice();

    expect($invoice->type)->toBe(InvoiceType::Invoice)
        ->and($invoice->number)->toBe('FAC-'.now()->year.'-0001')
        ->and($invoice->client_name)->toBe('Concordia')
        ->and($invoice->lines)->toHaveCount(1)
        ->and($invoice->lines->first()->description)->toBe('Création de logo')
        ->and($quote->fresh()->type)->toBe(InvoiceType::Quote)
        ->and($quote->fresh()->number)->toBe('DEV-'.now()->year.'-0002');
});

it('renders a pdf with the sample amounts', function () {
    $invoice = sampleInvoice();

    $html = view('invoices.pdf', ['invoice' => $invoice])->render();

    expect($html)->toContain('FACTURE')
        ->toContain('Concordia')
        ->toContain('Création de logo')
        ->toContain(InvoiceTotals::formatMoney(4650))
        ->toContain(InvoiceTotals::formatMoney(930))
        ->toContain(InvoiceTotals::formatMoney(5580))
        ->toContain('MERCI DE VOTRE CONFIANCE');

    expect(app(InvoicePdf::class)->output($invoice))->toStartWith('%PDF');
});

it('emails the invoice pdf and marks it as sent', function () {
    Mail::fake();

    $invoice = sampleInvoice();

    app(InvoiceMailer::class)->send(
        $invoice,
        'client@example.com',
        'Votre facture',
        'Bonjour, voici la facture.',
    );

    Mail::assertSent(InvoiceMail::class, function (InvoiceMail $mail) use ($invoice): bool {
        $attachments = $mail->attachments();

        return $mail->hasTo('client@example.com')
            && $mail->hasSubject('Votre facture')
            && count($attachments) === 1
            && $attachments[0]->as === $invoice->filename()
            && $attachments[0]->mime === 'application/pdf';
    });

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Sent)
        ->and($invoice->sent_at)->not->toBeNull();
});

it('downloads the invoice pdf from the consultation page', function () {
    $this->actingAs(User::factory()->admin()->create());

    $invoice = sampleInvoice();

    Livewire::test(ViewInvoice::class, ['record' => $invoice->getKey()])
        ->callAction('downloadPdf')
        ->assertFileDownloaded($invoice->filename());
});

it('shows billing only to admins', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ListInvoices::class)->assertForbidden();
    Livewire::test(BillingSettings::class)->assertForbidden();
});

it('lets an admin create a document and send it from the page', function () {
    Mail::fake();

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(BillingSettings::class)
        ->fillForm([
            'issuer_name' => 'Renaud Fradin',
            'issuer_email' => 'hello@example.com',
            'payment_terms' => 'Paiement sous 30 jours',
            'thank_you' => 'MERCI DE VOTRE CONFIANCE',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(BillingSetting::current()->issuer_name)->toBe('Renaud Fradin');

    Livewire::test(CreateInvoice::class)
        ->assertSee('Ajouter une ligne')
        ->fillForm([
            'type' => InvoiceType::Quote->value,
            'number' => 'DEV-'.now()->year.'-0099',
            'issued_on' => now()->toDateString(),
            'vat_rate' => 20,
            'client_name' => 'Concordia',
            'client_email' => 'client@example.com',
            'lines' => [
                [
                    'description' => 'Création de logo',
                    'unit_price' => 900,
                    'quantity' => 1,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $invoice = Invoice::query()->where('number', 'DEV-'.now()->year.'-0099')->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->type)->toBe(InvoiceType::Quote)
        ->and($invoice->issuer_name)->toBe('Renaud Fradin')
        ->and($invoice->lines)->toHaveCount(1)
        ->and($invoice->formattedTotal())->toBe(InvoiceTotals::formatMoney(1080));

    Livewire::test(ViewInvoice::class, ['record' => $invoice->getKey()])
        ->assertSee('Télécharger le PDF')
        ->assertSee('Envoyer par e-mail')
        ->assertSee('Convertir en facture')
        ->fillForm([
            'to' => 'client@example.com',
            'subject' => 'Votre devis',
            'body' => 'Bonjour, voici le devis.',
        ], 'emailForm')
        ->call('sendInvoice')
        ->assertNotified();

    Mail::assertSent(InvoiceMail::class, function (InvoiceMail $mail): bool {
        return $mail->hasTo('client@example.com')
            && $mail->attachments()[0]->mime === 'application/pdf';
    });

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Sent);

    Livewire::test(ListInvoices::class)
        ->assertSee('DEV-'.now()->year.'-0099')
        ->assertSee('Concordia');
});

function sampleInvoice(): Invoice
{
    $invoice = Invoice::factory()->create([
        'type' => InvoiceType::Invoice,
        'number' => 'FAC-'.now()->year.'-0007',
        'issued_on' => '2026-01-06',
        'client_name' => 'Concordia',
        'client_phone' => '123-456-7890',
        'client_address' => 'Anywhere St., Any City',
        'vat_rate' => 20,
        'issuer_name' => 'Renaud Fradin',
        'payment_order' => 'Renaud Fradin',
        'account_number' => '0123 4567 8901 2345',
        'payment_terms' => 'Paiement sous 30 jours',
        'thank_you' => 'MERCI DE VOTRE CONFIANCE',
    ]);

    foreach ([
        ['Création de logo', 900, 1],
        ['Conception d\'un flyer', 300, 1],
        ['Carte de visite', 300, 3],
        ['Illustration personnalisée', 200, 4],
        ['Refonte du site web', 1500, 1],
        ['Bannière publicitaire', 250, 1],
    ] as $position => [$description, $price, $quantity]) {
        $invoice->lines()->create([
            'description' => $description,
            'unit_price' => $price,
            'quantity' => $quantity,
            'position' => $position + 1,
        ]);
    }

    return $invoice->refresh();
}
