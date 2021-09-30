<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GeneralEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userFrom;
    public $subject;
    public $content;
    public $view;

    public function __construct($userFrom, $subject, $content, $view)
    {
        $this->userFrom = $userFrom;
        $this->subject = $subject;
        $this->content = $content;
        $this->view = $view;
    }

    public function build()
    {
        return $this->from($this->userFrom->email, $this->userFrom->siteSettings('name'))
                      ->subject($this->subject)
                      ->view($this->view)
                      ->with([
                              'content' => $this->content
                            ]);
    }
}
