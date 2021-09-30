<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VirtualizorNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userFrom;
    public $subject;
    public $details;
    public $view;

    public function __construct($userFrom, $subject, $details, $view)
    {
        $this->userFrom = $userFrom;
        $this->subject = $subject;
        $this->details = $details;
        $this->view = $view;
    }

    public function build()
    {
        return $this->from($this->userFrom->email, $this->userFrom->siteSettings('name'))
                      ->subject($this->subject)
                      ->view($this->view)
                      ->with([
                              'details' => $this->details
                            ]);
    }
}
