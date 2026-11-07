<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdf
{
    public function output(Invoice $invoice): string
    {
        $invoice->loadMissing('lines');

        return Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4')->output();
    }
}
