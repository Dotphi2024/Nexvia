<?php

namespace App\Mail;

use App\Models\DspApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DspApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public DspApplication $dsp;
    public string $password;
    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(DspApplication $dsp, string $password, ?string $loginUrl = null)
    {
        $this->dsp = $dsp;
        $this->password = $password;
        $this->loginUrl = $loginUrl ?: url('/dsp/login');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('development@dotphi.com', 'NEXVIA™ DSP Network'),
            replyTo: [
                new Address('development@dotphi.com', 'NEXVIA™ Partner Support')
            ],
            subject: 'Approved: Your NEXVIA™ DSP Partner Login & Portal Access Details (' . $this->dsp->application_number . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.dsp_approved',
            with: [
                'dsp'       => $this->dsp,
                'password'  => $this->password,
                'loginUrl'  => $this->loginUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
