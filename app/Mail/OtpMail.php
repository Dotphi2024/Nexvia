<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $otp;
    public string $purpose;
    public int $validMinutes;

    /**
     * Create a new message instance.
     *
     * @param string $name
     * @param string $otp
     * @param string $purpose 'login'|'registration'|'password_reset'|'resend'
     * @param int $validMinutes
     */
    public function __construct(string $name, string $otp, string $purpose = 'login', int $validMinutes = 10)
    {
        $this->name         = $name;
        $this->otp          = $otp;
        $this->purpose      = $purpose;
        $this->validMinutes = $validMinutes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName     = config('mail.from.name', 'NEXVIA');
        $fromAddress = config('mail.from.address', 'nexviadls@gmail.com');

        $subjectMap = [
            'registration'   => "Your {$appName} Registration Verification Code: {$this->otp}",
            'password_reset' => "Reset Your {$appName} Password: {$this->otp}",
            'resend'         => "Your Resent {$appName} Verification Code: {$this->otp}",
            'login'          => "Your {$appName} Login Verification Code: {$this->otp}",
        ];

        $subject = $subjectMap[$this->purpose] ?? "Your {$appName} Verification Code: {$this->otp}";

        return new Envelope(
            from: new Address($fromAddress, $appName),
            replyTo: [
                new Address($fromAddress, "{$appName} Support")
            ],
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'name'         => $this->name,
                'otp'          => $this->otp,
                'purpose'      => $this->purpose,
                'validMinutes' => $this->validMinutes,
            ],
        );
    }
}
