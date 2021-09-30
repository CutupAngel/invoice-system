<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DirectAdminImportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $userFrom;
    public $type;

    public function __construct($userFrom, $type, $content)
    {
        $this->content = $content;
        $this->userFrom = $userFrom;
        $this->type = $type;
    }

    public function build()
    {
        $view = '';
        if($this->type == 'signup' || $this->type == 'service_welcome_email')
        {
            $view = 'Integrations.mail.directadmin.welcomeEmail';
        }
        else if($this->type == 'automated')
        {
            $view = 'Integrations.mail.directadmin.automatedPasswordResetEmail';
        }

        return $this->from($this->userFrom->email)
                      ->subject('Mail: ' . $this->type)
                      ->view($view)
                      ->with([
                              'content' => $this->content
                            ]);
    }
}
