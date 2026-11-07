<?php

namespace App\Services\Invoices;

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;

class InvoiceMailer
{
    public function send(Invoice $invoice, string $to, string $subject, string $body): void
    {
        Mail::to($to)->send(new InvoiceMail($invoice, $body, $subject));

        $invoice->forceFill([
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ])->save();
    }
}
