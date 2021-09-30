<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userFromEmail;
    public $userFromName;
    public $subject;
    public $content;
    public $view;

    public function __construct($userFromEmail, $userFromName, $subject, $content, $view)
    {
        $this->userFromEmail = $userFromEmail;
        $this->userFromName = $userFromName;
        $this->subject = $subject;
        $this->content = $content;
        $this->view = $view;
    }

    public function build()
    {
        return $this->from($this->userFromEmail, $this->userFromName)
                      ->subject($this->subject)
                      ->view($this->view)
                      ->with($this->content);
    }
}
