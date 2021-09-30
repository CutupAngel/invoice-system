<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userFrom;
    public $subject;
    public $order;
    public $view;

    public function __construct($userFrom, $subject, $order, $view)
    {
        $this->userFrom = $userFrom;
        $this->subject = $subject;
        $this->order = $order;
        $this->view = $view;
    }

    public function build()
    {
        return $this->from($this->userFrom->email, $this->userFrom->siteSettings('name'))
                      ->subject($this->subject)
                      ->view($this->view)
                      ->with([
                              'order' => $this->order
                            ]);
    }
}
