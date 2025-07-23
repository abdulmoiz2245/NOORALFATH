<?php

namespace App\Mail;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quotation $quotation,
        public string $customMessage = '',
        public bool $attachPdf = true
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quotation ' . $this->quotation->quote_number . ' from Noor Al-Fath',
            to: [$this->quotation->client->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation',
            with: [
                'quotation' => $this->quotation,
                'customMessage' => $this->customMessage,
            ]
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->attachPdf) {
            // Generate PDF content
            $pdf = Pdf::loadView('pdf.quotation', ['quotation' => $this->quotation]);
            
            $attachments[] = Attachment::fromData(
                fn () => $pdf->output(),
                'quotation-' . $this->quotation->quote_number . '.pdf'
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
