<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Customer $customer;

    /**
     * Create a new message instance.
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName     = config('mail.from.name', 'NEXVIA');
        $fromAddress = config('mail.from.address', 'nexviadls@gmail.com');

        return new Envelope(
            from: new Address($fromAddress, $appName),
            replyTo: [
                new Address($fromAddress, "{$appName} Support")
            ],
            subject: "Welcome to {$appName}, {$this->customer->name}!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'customerName' => $this->customer->name,
                'phone'        => $this->customer->phone,
                'email'        => $this->customer->email,
                'referralCode' => $this->customer->referral_code,
            ],
        );
    }
}
