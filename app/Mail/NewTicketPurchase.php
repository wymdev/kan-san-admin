<?php

namespace App\Mail;

use App\Models\TicketPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewTicketPurchase extends Mailable
{
    use Queueable, SerializesModels;

    public $purchase;

    public function __construct(TicketPurchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function build()
    {
        return $this->subject('New Lottery Ticket Purchase Pending Approval')
            ->view('emails.new_ticket_purchase');
    }
}
