<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CpanelImportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userFrom;
    public $type;
    public $details;
    public $cpanelServer;

    public function __construct($userFrom, $type, $details, $cpanelServer)
    {
        $this->userFrom = $userFrom;
        $this->type = $type;
        $this->details = $details;
        $this->cpanelServer = $cpanelServer;
    }

    public function build()
    {
        $view = '';
        if($this->type == 'signup' || $this->type == 'service_welcome_email')
        {
            $view = 'Integrations.mail.cpanel.welcomeEmail';
        }
        else if($this->type == 'automated')
        {
            $view = 'Integrations.mail.cpanel.automatedPasswordResetEmail';
        }

        return $this->from($this->userFrom->email)
                      ->subject('Mail: ' . $this->type)
                      ->view($view)
                      ->with([
                              'details' => $this->details,
                              'cpanelServer' => $this->cpanelServer
                            ]);
    }
}
