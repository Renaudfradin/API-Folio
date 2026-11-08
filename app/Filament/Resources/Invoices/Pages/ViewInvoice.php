<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\InvoiceType;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Services\Invoices\InvoiceMailer;
use App\Services\Invoices\InvoicePdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $emailData = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->fillEmailForm();
    }

    public function getTitle(): string
    {
        /** @var Invoice $invoice */
        $invoice = $this->getRecord();

        return $invoice->type->getLabel().' '.$invoice->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Télécharger le PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): StreamedResponse {
                    /** @var Invoice $invoice */
                    $invoice = $this->getRecord();

                    return response()->streamDownload(function () use ($invoice): void {
                        echo app(InvoicePdf::class)->output($invoice);
                    }, $invoice->filename(), [
                        'Content-Type' => 'application/pdf',
                    ]);
                }),
            Action::make('convertToInvoice')
                ->label('Convertir en facture')
                ->icon('heroicon-o-document-duplicate')
                ->visible(fn (): bool => $this->getRecord()->type === InvoiceType::Quote)
                ->action(function (): void {
                    /** @var Invoice $quote */
                    $quote = $this->getRecord();
                    $invoice = $quote->convertToInvoice();

                    Notification::make()
                        ->title('Facture créée')
                        ->success()
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('view', ['record' => $invoice]));
                }),
            EditAction::make(),
        ];
    }

    public function emailForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Envoyer par e-mail')
                    ->description('Le PDF est joint au message. L’envoi utilise la configuration mail du site.')
                    ->schema([
                        TextInput::make('to')
                            ->label('Destinataire')
                            ->email()
                            ->required(),
                        TextInput::make('subject')
                            ->label('Objet')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('body')
                            ->label('Message')
                            ->rows(6)
                            ->required(),
                    ])
                    ->footerActions([
                        Action::make('sendInvoice')
                            ->label('Envoyer')
                            ->icon('heroicon-o-paper-airplane')
                            ->action(fn () => $this->sendInvoice()),
                    ]),
            ])
            ->statePath('emailData');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getInfolistContentComponent(),
                EmbeddedSchema::make('emailForm'),
            ]);
    }

    public function sendInvoice(): void
    {
        $data = $this->emailForm->getState();

        /** @var Invoice $invoice */
        $invoice = $this->getRecord();

        try {
            app(InvoiceMailer::class)->send(
                $invoice,
                $data['to'],
                $data['subject'],
                $data['body'],
            );
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('L’e-mail n’a pas pu être envoyé')
                ->danger()
                ->send();

            return;
        }

        $invoice->refresh();

        Notification::make()
            ->title('E-mail envoyé')
            ->success()
            ->send();
    }

    protected function fillEmailForm(): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->getRecord();

        $this->emailForm->fill([
            'to' => $invoice->client_email,
            'subject' => $invoice->defaultMailSubject(),
            'body' => $invoice->defaultMailBody(),
        ]);
    }
}
