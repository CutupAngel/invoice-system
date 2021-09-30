<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CpanelNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userFrom;
    public $subject;
    public $details;
    public $cpanelServer;
    public $view;

    public function __construct($userFrom, $subject, $details, $cpanelServer, $view)
    {
        $this->userFrom = $userFrom;
        $this->subject = $subject;
        $this->details = $details;
        $this->cpanelServer = $cpanelServer;
        $this->view = $view;
    }

    public function build()
    {
        return $this->from($this->userFrom->email, $this->userFrom->siteSettings('name'))
                      ->subject($this->subject)
                      ->view($this->view)
                      ->with([
                              'details' => $this->details,
                              'cpanelServer' => $this->cpanelServer
                            ]);
    }
}
