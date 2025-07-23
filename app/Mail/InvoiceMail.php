<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $customMessage = '',
        public bool $attachPdf = true
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number . ' from Noor Al-Fath',
            to: [$this->invoice->client->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'customMessage' => $this->customMessage,
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->attachPdf) {
            // Generate PDF content
            $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $this->invoice]);
            
            $attachments[] = Attachment::fromData(
                fn () => $pdf->output(),
                'invoice-' . $this->invoice->invoice_number . '.pdf'
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
