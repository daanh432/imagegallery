<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;
    private $submission;

    /**
     * Create a new message instance.
     *
     * @param $validated
     */
    public function __construct($validated)
    {
        $this->submission = (object)$validated;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo($this->submission->email)->markdown('emails.ContactFormEmail')->with([
            'submission' => $this->submission
        ]);
    }
}
