<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function build()
    {
        return $this->subject('Ph.D. Application Submitted — Rathinam Global Deemed to be University')
            ->markdown('emails.application-submitted');
    }
}
