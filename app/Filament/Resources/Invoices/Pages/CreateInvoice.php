<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\BillingSetting;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $type = InvoiceType::Invoice;

        $this->form->fill([
            'type' => $type->value,
            'issued_on' => now()->toDateString(),
            'number' => Invoice::nextNumber($type),
            'vat_rate' => 20,
            'status' => InvoiceStatus::Draft->value,
            ...BillingSetting::current()->issuerAttributes(),
        ]);

        $this->callHook('afterFill');
    }

    protected function getRedirectUrl(): string
    {
        return InvoiceResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
