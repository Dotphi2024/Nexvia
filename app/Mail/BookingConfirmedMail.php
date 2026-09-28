<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName     = config('mail.from.name', 'NEXVIA');
        $fromAddress = config('mail.from.address', 'nexviadls@gmail.com');
        $bookingNo   = $this->booking->booking_number ?? $this->booking->id;

        return new Envelope(
            from: new Address($fromAddress, $appName),
            replyTo: [
                new Address($fromAddress, "{$appName} Orders")
            ],
            subject: "Booking Confirmed: {$this->booking->product_name} (#{$bookingNo})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $booking = $this->booking;

        $shipping = trim(($booking->shipping_address ?? '') . ', ' . ($booking->city ?? '') . ', ' . ($booking->state ?? '') . ' - ' . ($booking->pincode ?? ''), ', - ');

        return new Content(
            view: 'emails.booking_confirmation',
            with: [
                'customerName'    => $booking->customer_name ?? 'Valued Customer',
                'bookingNumber'   => $booking->booking_number ?? "#{$booking->id}",
                'productName'     => $booking->product_name ?? 'NEXVIA Product',
                'quantity'        => $booking->quantity ?? 1,
                'amountPaid'      => $booking->paid_amount ?? $booking->total_amount ?? 0,
                'balanceAmount'   => $booking->remaining_balance ?? 0,
                'dueDate'         => $booking->balance_due_date ? date('d M Y', strtotime($booking->balance_due_date)) : null,
                'shippingAddress' => $shipping,
            ],
        );
    }
}
