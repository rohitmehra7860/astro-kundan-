<?php

namespace App\Mail;

use App\Modules\ContactLeads\Models\ContactLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactLeadNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ContactLead $lead;

    /**
     * Create a new message instance.
     */
    public function __construct(ContactLead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Contact Form Submission: ' . ($this->lead->subject ?? 'General Inquiry'))
            ->view('emails.contact-lead-notification');
    }
}
